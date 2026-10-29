#!/bin/bash

set -eo pipefail

# Describe a matrix list from tests/phpunit-matrix.json, so this help cannot
# drift from the cells that actually run. Called before the cd below, so it
# resolves the file from $0 rather than the caller's working directory.
describe_matrix_list() {
  local list_name="$1"
  local matrix_file
  matrix_file="$(cd "$(dirname "$0")/.." && pwd)/tests/phpunit-matrix.json"

  if ! command -v jq >/dev/null 2>&1 || [ ! -f "$matrix_file" ]; then
    echo "(install jq to list the cells)"
    return
  fi

  jq -r --arg l "$list_name" '
    .[$l].cells
    | "\(length)-cell: " + (
        map( "{PHP \(.php) / WP \(.wp)" + ( if .ms == 1 then " multisite" else "" end ) + "}" )
        | join( ", " )
      )
  ' "$matrix_file"
}

show_help() {
  printf -- 'Usage: %s [OPTIONS]\n\n' "$0";

  echo "Run the PHPUnit compatibility matrix against multiple WP/PHP/multisite combinations.";
  echo "Each cell is driven through bin/run-tests-local.sh, which manages its own Docker lifecycle.";
  echo "";
  echo "With no options, runs the default matrix list (pr). Pass -p/-w/-m to run a single cell instead.";
  echo "";
  echo "Matrix Options:";
  printf -- '    --matrix[=LIST]\tRun all cells in a matrix list from tests/phpunit-matrix.json.\n';
  printf -- '\t\t\tLIST: pr (default), full, coverage.\n';
  printf -- '\t\t\tThis is also what a bare invocation (no options) runs.\n';
  printf -- '\t\t\tRequires: jq, Docker.\n';
  printf -- '    --retry\t\tRetry failed matrix cells once after the initial pass.\n';
  printf -- '-f, --filter TEXT\tFilter tests by name (passed to run-tests-local.sh -f / PHPUnit --filter).\n';
  printf -- '-g, --group NAME\tRun only tests in a PHPUnit @group (passed to run-tests-local.sh -g / PHPUnit --group).\n';
  printf -- '-x, --extra TEXT\tExtra plugins to include (passed to run-tests-local.sh -x, e.g. "recurring").\n';
  echo "";
  echo "Single-cell Options (run ONE cell directly when --matrix is NOT given):";
  printf -- '-p, --php VERSION\tPHP version for the Docker container (Default: 8.2).\n';
  printf -- '-w, --wp VERSION\tWordPress version (Default: latest). Use "latest" for the current release.\n';
  printf -- '-m, --multisite\t\tRun the single cell as multisite.\n';
  echo "";
  printf -- '-h, --help\t\tShow this help.\n';
  echo "";
  echo "Matrix list guide (mirrors .github/workflows/CI.yml, read from tests/phpunit-matrix.json):";
  echo "  pr        Fast gate — runs on every PR.";
  echo "            $(describe_matrix_list pr)";
  echo "  full      Broad check — runs on push to main/release.";
  echo "            $(describe_matrix_list full)";
  echo "  coverage  The cell the CI coverage job runs, with Xdebug.";
  echo "            $(describe_matrix_list coverage)";
  echo "";
  echo "Examples:";
  echo "  $0                                   # Default: PR matrix";
  echo "  $0 --matrix                          # PR matrix, explicit";
  echo "  $0 --matrix=pr                       # Same as above, explicit list name";
  echo "  $0 --matrix=full                     # Full matrix";
  echo "  $0 --matrix --retry                  # PR matrix with auto-retry on failures";
  echo "  $0 --matrix --filter 'Discounts'     # PR matrix filtered to 'Discounts' tests";
  echo "  $0 --matrix --group edd_orders       # PR matrix limited to the edd_orders group";
  echo "  $0 -p 8.2 -w latest                  # Single cell: PHP 8.2 / WP latest";
  echo "  $0 -p 8.3 -w latest -m -f 'Cart'     # Single multisite cell, filtered";
}

# Initialize variables
FILTER=""
GROUP=""
EXTRA=""
MATRIX_MODE=false
MATRIX_LIST=""
MATRIX_RETRY=false
CELL_PHP=""
CELL_WP=""
CELL_MS=0

# Portable manual argument parsing (POSIX/bash, no GNU getopt — works on macOS BSD too).
# Supports long `--opt value` and `--opt=value`, short `-x value`, and `-h/--help`.
# `--matrix` is an optional-argument flag: bare `--matrix` uses the default list,
# `--matrix=LIST` selects a named list (a following bare word is NOT consumed as its value).
# require_arg ensures a value-taking flag was actually given an argument.
require_arg() {
  # $1 = flag name, $2 = candidate value (passed as "${2-}", so empty when missing).
  case "${2-}" in
    '' | -* )
      echo "Error: option '$1' requires an argument." >&2
      show_help
      exit 1
      ;;
  esac
}

while [ $# -gt 0 ]; do
  case "$1" in
    --php=*|-p=* )
        CELL_PHP="${1#*=}"
        shift
        ;;
    --php|-p )
        require_arg "$1" "${2-}"
        CELL_PHP="$2"
        shift 2
        ;;
    --wp=*|-w=* )
        CELL_WP="${1#*=}"
        shift
        ;;
    --wp|-w )
        require_arg "$1" "${2-}"
        CELL_WP="$2"
        shift 2
        ;;
    --multisite|-m )
        CELL_MS=1
        shift
        ;;
    --filter=*|-f=* )
        FILTER="${1#*=}"
        shift
        ;;
    --filter|-f )
        require_arg "$1" "${2-}"
        FILTER="$2"
        shift 2
        ;;
    --group=*|-g=* )
        GROUP="${1#*=}"
        shift
        ;;
    --group|-g )
        require_arg "$1" "${2-}"
        GROUP="$2"
        shift 2
        ;;
    --extra=*|-x=* )
        EXTRA="${1#*=}"
        shift
        ;;
    --extra|-x )
        require_arg "$1" "${2-}"
        EXTRA="$2"
        shift 2
        ;;
    --retry )
        MATRIX_RETRY=true
        shift
        ;;
    --matrix=* )
        # Optional-argument flag: --matrix=LIST selects a named list.
        MATRIX_MODE=true
        MATRIX_LIST="${1#*=}"
        shift
        ;;
    --matrix )
        # Bare --matrix uses the default list; a following bare word is NOT consumed.
        MATRIX_MODE=true
        shift
        ;;
    --help|-h )
        show_help
        exit 0
        ;;
    --)
        shift
        break
        ;;
    -*)
        echo "Error: unknown option '$1'." >&2
        show_help
        exit 1
        ;;
    *)
        echo "Error: unexpected argument '$1'." >&2
        show_help
        exit 1
        ;;
  esac
done

# Single source for the Docker-daemon-running check, sourced with no side effects.
# shellcheck source=bin/ensure-docker-running.sh
. "$(cd "$(dirname "$0")" && pwd)/ensure-docker-running.sh"

cd "$(dirname "$0")/.."

# Whether the most recent run_cell actually executed PHPUnit tests.
# Set by run_cell after scanning the cell output for a PHPUnit results line.
CELL_RAN_TESTS=0

# Run a single cell through bin/run-tests-local.sh.
# Args: php wp ms (ms is 0 or 1). Honors the global FILTER/GROUP/EXTRA.
# Returns the exit code of run-tests-local.sh.
# Side effect: sets CELL_RAN_TESTS=1 iff the cell output contains a PHPUnit
# results line proving tests actually executed (guards against a clean exit
# code from a run that bailed during install/setup before any test ran).
run_cell() {
    local php="$1" wp="$2" ms="$3"
    local args=( -p "$php" -w "$wp" )

    # Only append -m for multisite cells (run-tests-local.sh has no "off" flag).
    if [ "$ms" = "1" ]; then
        args+=( -m )
    fi
    if [ -n "$FILTER" ]; then
        args+=( -f "$FILTER" )
    fi
    if [ -n "$GROUP" ]; then
        args+=( -g "$GROUP" )
    fi
    if [ -n "$EXTRA" ]; then
        args+=( -x "$EXTRA" )
    fi

    # run-tests-local.sh starts/tears down its own Docker containers per invocation,
    # so we just call it and capture the exit code — no compose teardown here.
    # Tee the output so we can both stream it live AND scan it afterwards to
    # confirm tests actually ran (the exit code alone is not trustworthy: a run
    # that crashes during install can still surface a zero status).
    local out_file
    out_file=$(mktemp -t phpunit-cell.XXXXXX)
    local rc=0
    set +e
    ./bin/run-tests-local.sh "${args[@]}" 2>&1 | tee "$out_file"
    rc=${PIPESTATUS[0]}
    set -e

    # A genuine PHPUnit run prints either "Tests: <N>, ..." (with failures/skips)
    # or "OK (<N> tests, ...)" (all green). Require N > 0 so a "Tests: 0" / empty
    # run is NOT treated as having executed tests.
    CELL_RAN_TESTS=0
    if grep -Eq '^OK \([1-9][0-9]* test' "$out_file" \
        || grep -Eq '^Tests: [1-9][0-9]*[,.]' "$out_file"; then
        CELL_RAN_TESTS=1
    fi

    rm -f "$out_file"
    return $rc
}

# Run the full matrix loop. Iterates cells from tests/phpunit-matrix.json[LIST_NAME] serially.
run_matrix() {
    local list_name="${1:-pr}"
    local matrix_file="tests/phpunit-matrix.json"

    if [ ! -f "$matrix_file" ]; then
        echo "Error: $matrix_file not found. Expected at plugin root."
        exit 1
    fi

    # Validate list name exists in matrix.json and has a cells array.
    if ! jq -e ".[\"$list_name\"].cells" "$matrix_file" >/dev/null 2>&1; then
        echo "Error: Matrix list '$list_name' not found in $matrix_file (or missing 'cells' array)."
        echo "Available lists: $(jq -r 'keys[]' "$matrix_file" | tr '\n' ' ')"
        exit 1
    fi

    local cells_json
    cells_json=$(jq -c ".[\"$list_name\"].cells[]" "$matrix_file")

    local pass_count=0
    local fail_count=0
    local results=()
    local failed_cells=()
    local start_time
    start_time=$(date +%s)

    # Read EDD version from plugin header.
    local edd_version
    edd_version=$(grep -m1 '^ \* Version:' easy-digital-downloads.php 2>/dev/null | sed 's/.*Version: *//' || echo "unknown")

    echo ""
    echo "============================================"
    echo "  PHPUnit matrix run: $list_name"
    echo "  $(jq ".[\"$list_name\"].cells | length" "$matrix_file") cells — serial execution"
    echo "  EDD: $edd_version"
    echo "============================================"
    echo ""

    # Read cells from FD 3, not stdin — run-tests-local.sh (docker compose) consumes
    # stdin and would otherwise exhaust the here-string after one iteration.
    while IFS= read -r cell_json <&3; do
        local php wp ms cell_id
        php=$(echo "$cell_json" | jq -r .php)
        wp=$(echo "$cell_json" | jq -r .wp)
        ms=$(echo "$cell_json" | jq -r '.ms // 0')
        # Readable cell_id: wp-phpX.Y with a -ms suffix for multisite cells.
        cell_id="${wp}-php${php}"
        [ "$ms" = "1" ] && cell_id="${cell_id}-ms"

        echo ""
        echo "=== Matrix cell: $cell_id (WP $wp / PHP $php / Multisite $ms / EDD $edd_version) ==="

        local cell_rc=0
        run_cell "$php" "$wp" "$ms" || cell_rc=$?

        if [ $cell_rc -eq 0 ] && [ "$CELL_RAN_TESTS" = "1" ]; then
            pass_count=$((pass_count + 1))
            results+=("PASS  $cell_id")
        elif [ $cell_rc -eq 0 ]; then
            # Clean exit but no PHPUnit results line — the suite never actually
            # ran (install/setup bailed before any test). Treat as a FAIL so a
            # zero-test cell can never be reported as a false PASS.
            fail_count=$((fail_count + 1))
            results+=("FAIL  $cell_id (no tests executed — install/setup likely failed)")
            failed_cells+=("$cell_json")
        else
            fail_count=$((fail_count + 1))
            results+=("FAIL  $cell_id")
            failed_cells+=("$cell_json")
        fi

    done 3<<< "$cells_json"

    # --- Retry failed cells once (opt-in via --retry) ---
    if [ "$MATRIX_RETRY" = true ] && [ ${#failed_cells[@]} -gt 0 ]; then
        echo ""
        echo "============================================"
        echo "  Retrying ${#failed_cells[@]} failed cell(s)..."
        echo "============================================"

        for cell_json in "${failed_cells[@]}"; do
            local php wp ms cell_id
            php=$(echo "$cell_json" | jq -r .php)
            wp=$(echo "$cell_json" | jq -r .wp)
            ms=$(echo "$cell_json" | jq -r '.ms // 0')
            cell_id="${wp}-php${php}"
            [ "$ms" = "1" ] && cell_id="${cell_id}-ms"

            echo ""
            echo "=== RETRY: $cell_id (WP $wp / PHP $php / Multisite $ms / EDD $edd_version) ==="

            local cell_rc=0
            run_cell "$php" "$wp" "$ms" || cell_rc=$?

            if [ $cell_rc -eq 0 ] && [ "$CELL_RAN_TESTS" = "1" ]; then
                # Flip FAIL → PASS in results. The original FAIL entry may carry a
                # reason suffix (e.g. "no tests executed…"), so match on a prefix.
                pass_count=$((pass_count + 1))
                fail_count=$((fail_count - 1))
                for i in "${!results[@]}"; do
                    case "${results[$i]}" in
                        "FAIL  $cell_id" | "FAIL  $cell_id "* )
                            results[$i]="PASS  $cell_id (retry)"
                            break
                            ;;
                    esac
                done
                echo "=== RETRY PASSED: $cell_id ==="
            else
                echo "=== RETRY FAILED: $cell_id ==="
            fi
        done
    fi

    local end_time elapsed_total elapsed_s elapsed_m
    end_time=$(date +%s)
    elapsed_total=$(( end_time - start_time ))
    elapsed_m=$(( elapsed_total / 60 ))
    elapsed_s=$(( elapsed_total % 60 ))

    echo ""
    echo "============================================"
    echo "  PHPUnit Matrix Summary: $list_name (EDD $edd_version)"
    echo "============================================"
    printf '%s\n' "${results[@]}"
    echo ""
    echo "Passed: $pass_count / $((pass_count + fail_count))  (${elapsed_m}m ${elapsed_s}s total)"
    echo ""

    [ $fail_count -eq 0 ] || exit 1
}

# Main execution

# Preflight: jq is required for both single-cell and matrix modes (matrix reads the
# JSON; single-cell mode keeps the dependency consistent).
command -v jq >/dev/null 2>&1 || { echo "Error: jq is required. Install with: brew install jq (macOS), sudo apt-get install jq (Linux), choco install jq (Windows), or see https://jqlang.github.io/jq/download/"; exit 2; }

# Ensure Docker is running — both modes drive Docker via run-tests-local.sh.
ensure_docker_running

# Default (no single-cell flags, no --matrix): run the default matrix list.
# Running the script bare exercises the matrix (the fast PR gate) rather than a
# single cell — single cells are an explicit opt-in via -p/-w/-m.
if [ "$MATRIX_MODE" = false ] && [ -z "$CELL_PHP" ] && [ -z "$CELL_WP" ] && [ "$CELL_MS" = "0" ]; then
    MATRIX_MODE=true
fi

# Matrix mode: run all cells in the named list, then exit.
if [ "$MATRIX_MODE" = true ]; then
    # Default list for --matrix with no argument (and for a bare invocation) is pr.
    run_matrix "${MATRIX_LIST:-pr}"
    exit $?
fi

# Single-cell mode: at least one of -p/-w/-m was given, run ONE cell directly.
# Defaults mirror run-tests-local.sh (PHP 8.2 / WP latest).
CELL_PHP="${CELL_PHP:-8.2}"
CELL_WP="${CELL_WP:-latest}"

cell_id="${CELL_WP}-php${CELL_PHP}"
[ "$CELL_MS" = "1" ] && cell_id="${cell_id}-ms"

echo ""
echo "=== Single cell: $cell_id (WP $CELL_WP / PHP $CELL_PHP / Multisite $CELL_MS) ==="

cell_rc=0
run_cell "$CELL_PHP" "$CELL_WP" "$CELL_MS" || cell_rc=$?

echo ""
echo "============================================"
echo "  PHPUnit Single-cell Summary"
echo "============================================"
if [ $cell_rc -eq 0 ] && [ "$CELL_RAN_TESTS" = "1" ]; then
    echo "PASS  $cell_id"
elif [ $cell_rc -eq 0 ]; then
    echo "FAIL  $cell_id (no tests executed — install/setup likely failed)"
    cell_rc=1
else
    echo "FAIL  $cell_id"
fi
echo ""

exit $cell_rc
