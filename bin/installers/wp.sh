#!/usr/bin/env bash

# Plain `set -e`, not `-eo pipefail`: the version lookups below read a no-match
# grep as "no such release" and fall through, which pipefail would turn fatal.
set -e

WP_VERSION="$1"
WP_CORE_DIR="$2"
WP_TESTS_DIR="$3"
DB_NAME="$4"
DB_USER="$5"
DB_PASS="$6"
DB_HOST="$7"

# DB_PASS is deliberately absent from this check: the test database runs without
# a password, so an empty value is valid.
if [[ -z "${WP_VERSION}" ]] || [[ -z "${WP_CORE_DIR}" ]] || [[ -z "${WP_TESTS_DIR}" ]] || [[ -z "${DB_NAME}" ]] || [[ -z "${DB_USER}" ]] || [[ -z "${DB_HOST}" ]]; then
	echo "Usage: $0 <wp_version> <wp_core_dir> <wp_tests_dir> <db_name> <db_user> <db_pass> <db_host>"
	exit 1
fi

TMPDIR=${TMPDIR-/tmp}
TMPDIR=$(echo $TMPDIR | sed -e "s/\/$//")

download() {
    if [ `which curl` ]; then
        curl -fsSL --retry 3 --retry-connrefused --connect-timeout 20 "$1" > "$2";
    elif [ `which wget` ]; then
        wget -nv -O "$2" "$1"
    fi
}

fail() {
	echo "$1"
	exit 1
}

# The requested spec is often a moving target: `latest` and a bare X.Y both
# resolve elsewhere, and a cached core directory skips resolution entirely. Read
# the version out of the tree so the summary reports what actually landed.
installed_wp_version() {
	local version_file="$WP_CORE_DIR/wp-includes/version.php"

	if [[ ! -f "$version_file" ]]; then
		echo "$WP_VERSION"
		return
	fi

	grep -o "wp_version = '[^']*" "$version_file" | sed "s/.*'//"
}

# Newest released patch in an X.Y line, per the wordpress.org API. Empty when the
# line has been branched but never shipped.
latest_in_line() {
	local line_escaped
	line_escaped=$(echo "$1" | sed 's/\./\\./g')
	download https://api.wordpress.org/core/version-check/1.7/ "$TMPDIR/wp-latest.json" || return 1
	grep -o '"version":"'"$line_escaped"'[^"]*' "$TMPDIR/wp-latest.json" | sed 's/"version":"//' | head -1 || true
}

# wordpress.org names an X.Y.0 release plain X.Y, but wordpress-develop tags are
# always three-part.
release_tag() {
	if [[ $1 =~ ^[0-9]+\.[0-9]+$ ]]; then
		echo "refs/tags/$1.0"
	else
		echo "refs/tags/$1"
	fi
}

# Fetch tests/phpunit/{includes,data} and the config sample from a
# wordpress-develop archive. No runner image ships Subversion any more.
download_test_suite() {
	local ref="$WP_TESTS_REF"
	local top archive
	top="wordpress-develop-${ref##*/}"
	archive="$TMPDIR/wordpress-develop.tar.gz"

	echo "Installing the WordPress test suite from ${ref}"
	download "https://codeload.github.com/WordPress/wordpress-develop/tar.gz/${ref}" "$archive" \
		|| fail "Could not download the WordPress test suite for ${ref}. Check that the ref exists."

	tar -xzf "$archive" -C "$WP_TESTS_DIR" --strip-components=3 \
		"$top/tests/phpunit/includes" "$top/tests/phpunit/data" \
		|| fail "Could not extract the WordPress test suite for ${ref}."

	tar -xzOf "$archive" "$top/wp-tests-config-sample.php" > "$WP_TESTS_DIR/wp-tests-config-sample.php" \
		|| fail "Could not extract wp-tests-config-sample.php for ${ref}."

	rm -f "$archive"
}

# Resolve the requested spec once, so core and the test suite cannot disagree.
# A nightly build has no wordpress.org release archive.
if [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+\-(beta|RC)[0-9]+$ ]]; then
	WP_TESTS_REF="refs/heads/${WP_VERSION%\-*}"
	WP_CORE_ARCHIVE="wordpress-$WP_VERSION"
elif [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+$ ]]; then
	# The branch head can sit ahead of the newest release, which would pair core
	# with a suite it never shipped with. A branched-but-unreleased line has no tag.
	WP_LINE_LATEST="$(latest_in_line "$WP_VERSION")" \
		|| fail "Could not look up the newest WordPress ${WP_VERSION} release from api.wordpress.org."
	if [[ -n "$WP_LINE_LATEST" ]]; then
		WP_TESTS_REF="$(release_tag "$WP_LINE_LATEST")"
		WP_CORE_ARCHIVE="wordpress-$WP_LINE_LATEST"
	else
		WP_TESTS_REF="refs/heads/$WP_VERSION"
		WP_CORE_ARCHIVE="wordpress-$WP_VERSION"
	fi
elif [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
	WP_TESTS_REF="refs/tags/$WP_VERSION"
	WP_CORE_ARCHIVE="wordpress-${WP_VERSION%.0}"
elif [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
	WP_TESTS_REF='refs/heads/trunk'
	WP_CORE_ARCHIVE=''
else
	# https serves multiple offers, newest first.
	download https://api.wordpress.org/core/version-check/1.7/ "$TMPDIR/wp-latest.json" \
		|| fail "Could not look up the latest WordPress release from api.wordpress.org."
	LATEST_VERSION=$(grep -o '"version":"[^"]*' "$TMPDIR/wp-latest.json" | sed 's/"version":"//' | head -1)
	if [[ -z "$LATEST_VERSION" ]]; then
		echo "Latest WordPress version could not be found"
		exit 1
	fi
	WP_TESTS_REF="$(release_tag "$LATEST_VERSION")"
	WP_CORE_ARCHIVE="wordpress-$LATEST_VERSION"
fi

install_wp() {

	if [ -d "$WP_CORE_DIR" ]; then
		return;
	fi

	mkdir -p "$WP_CORE_DIR"

	if [[ -z "$WP_CORE_ARCHIVE" ]]; then
		# wordpress-develop keeps core under src/, so the nightly build is what
		# matches the built layout the mv below expects.
		local nightly_zip="$TMPDIR/wordpress-nightly.zip"
		local nightly_dir="$TMPDIR/wordpress-trunk"

		rm -rf "$nightly_dir"
		mkdir -p "$nightly_dir"
		download https://wordpress.org/nightly-builds/wordpress-latest.zip "$nightly_zip" \
			|| fail "Could not download the WordPress nightly build."
		unzip -q "$nightly_zip" -d "$nightly_dir" \
			|| fail "Could not extract the WordPress nightly build."
		mv "$nightly_dir"/wordpress/* "$WP_CORE_DIR"
		rm -rf "$nightly_zip" "$nightly_dir"
	else
		download "https://wordpress.org/${WP_CORE_ARCHIVE}.tar.gz" "$TMPDIR/wordpress.tar.gz" \
			|| fail "Could not download ${WP_CORE_ARCHIVE}. Check that the version exists."
		tar --strip-components=1 -zxmf "$TMPDIR/wordpress.tar.gz" -C "$WP_CORE_DIR" \
			|| fail "Could not extract ${WP_CORE_ARCHIVE}."
	fi
}

install_test_suite() {
	local core_dir="${WP_CORE_DIR%/}"

	if [ ! -f "$WP_TESTS_DIR/wp-tests-config-sample.php" ]; then
		rm -rf "$WP_TESTS_DIR"/{includes,data}
		mkdir -p "$WP_TESTS_DIR"
		download_test_suite
	fi

	sed -e "s:dirname( __FILE__ ) . '/src/':'$core_dir/':" \
		-e "s:__DIR__ . '/src/':'$core_dir/':" \
		-e "s/youremptytestdbnamehere/$DB_NAME/" \
		-e "s/yourusernamehere/$DB_USER/" \
		-e "s/yourpasswordhere/$DB_PASS/" \
		-e "s|localhost|${DB_HOST}|" \
		"$WP_TESTS_DIR"/wp-tests-config-sample.php > "$WP_TESTS_DIR"/wp-tests-config.php
}
install_wp
install_test_suite

echo "✔ WordPress $(installed_wp_version) and its test suite installed"
