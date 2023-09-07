#!/usr/bin/env bash
clear

# Process a given directory and run the heartbeat test on all files in that directory
# This needs to be delcared before it is called in the loop below.
process_directory() {
	dir=$1
	output=""
	tests_run=0

	# Setup a colleciton of failures
	failures=()

	printf "Checking directory: \e[1;36m${dir}\e[0m"
	printf "\n"

	# Recursively search the ../includes/admin directory for files containing the string "add_action( 'edd_" itterate over them and print the file name and line number.
	while read -r line ; do
		# Increment the tests_run variable
		tests_run=$((tests_run + 1))

		# Split the line into an array
		IFS=':' read -r -a array <<< "$line"
		file_line="${array[0]}:${array[1]}"
		action_run=$(echo "${array[2]}" | grep -o "'.*'" | sed "s/'//g" | sed "s/,.*//g" )

		# Remove all leading and trailing whitespace from array item 2, including tabs and newlines and assign it to full_action_line
		full_action_line=$(echo "${array[2]}" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//')

		# Out put a . for this itteration of the loop, knowing it will be replaced later.
		output+=" . "
		tput sc
		printf "\r${output}"
		tput rc

		# If the array element 2 contains `wp_ajax_edd` we call it slightly differently, so we need to check for that.
		if [[ $action_run == *"wp_ajax_edd"* ]]; then
			# Split the action_run variable into an array
			IFS='_' read -r -a array <<< "$action_run"
			# Remove the first element of the array
			unset array[0]
			# Join the array back together with an underscore
			action=$(IFS=_; echo "${array[*]}")

			# Run a cURL command curl https://<your_local_domain>/wp-admin/admin-ajax.php\?action\=heartbeat -X POST -d "edd-action=<action>"
			# Store the result of this check in a variable
			test_result=$(curl -s "$site_url/wp-admin/admin-ajax.php?action={$action}" -X POST)
		else
			action=$(echo "${array[2]}" | grep -o "'.*'" | sed "s/'//g" | sed "s/,.*//g" | sed "s/edd_//g")

			# Run a cURL command curl https://<your_local_domain>/wp-admin/admin-ajax.php\?action\=heartbeat -X POST -d "edd-action=<action>"
			# Store the result of this check in a variable
			test_result=$(curl -s "$site_url/wp-admin/admin-ajax.php?action=heartbeat" -X POST -d "edd-action=${action}")
		fi

		# If the result does not contain one of the following strings, it failed:
		# "wp-auth-check":false" - this happens when we return without outputting anything
		# "There has been a ctritical error on this website..." - This occurs when the hook is expecting some sort of passed values that doesn't exist due to it being a HTTP request
		#  "You do not have permission..." - When we run a `wp_die` with a capability check.
		# "Nonce verification failed." - When EDD dies after a nonce verification failes.
		# "0" - When we call an AJAX action, but nothing is run as our requirements are not met.
		#
		# Ideally, this would be in an array, but the 'fuzzy' checks on taht might product a false positive for the `0` string if any output contains a `0` as a substring of the result.
		if [[ $test_result != *"wp-auth-check\":false"* ]] && [[ $test_result != *"There has been a critical error on this website"* ]] && [[ $test_result != *"You do not have permission"* ]] && [[ $test_result != *"Nonce verification failed."* ]] && [[ $test_result != "0" ]] ; then
			# Replace just the last output chracter with a red ex, and update the output variable
			output=${output/ . /" \e[1;31m\xE2\x9C\x98\e[0m "}

			# Add the file, line number and full action to the failures array
			# Format is File:Line Number \n The full add_action line \n Output: <first 50 characters of output>
			failures+=("${file_line}\n${full_action_line}\n Output:\n ${test_result:0:50}")
		else
			# Replace just the last output character with a green check, and update the output variable
			output=${output/ . /" \e[1;32m\xE2\x9C\x94\e[0m "}
		fi
	done < <(grep -r -n "add_action( 'edd_\|add_action( 'wp_ajax_edd_" ${dir})

	# Do one last replacement of the results.
	printf "\r${output}"

	printf "\n"
	printf "%d tests run on \e[1;36m${dir}\e[0m." ${tests_run}
	printf "\n"

	# Print number of passed tests out of total tests.
	printf "\e[1;32m$((tests_run - ${#failures[@]})) tests passed.\e[0m"
	printf "\n"
	printf "\n";

	# If the failures array is not empty, print the failures and exit with a non-zero exit code
	if [ ${#failures[@]} -gt 0 ]; then
		printf "\e[1;31m$(( ${#failures[@]} )) tests failed:\e[0m"
		printf "\n"
		printf "The following actions failed to pass the heartbeat test:"
		printf "\n"
		# Itterate over the failures array and print each failure
		for failure in "${failures[@]}"; do
			printf "\e[1;31m${failure}\e[0m"
			printf "\n\n"
		done
		printf "\n"
	else
		# If the failures array is empty, print a success message and exit with a zero exit code
		printf "\e[1;32mAll actions passed the heartbeat test for \e[1;36m${dir}\e[0m!\e[0m"
		printf "\n"
		printf "\n"
	fi
}

# This does the work calling the process_directory function on each directory.
site_url=$(wp option get siteurl)

printf "Testing edd_action hooks for heartbeat exploits..."
printf "\n"
printf "\n"

# Store a list of directory paths to run the checks on.
directories=("includes/admin" "includes/gateways/stripe/includes/admin" "includes/gateways/paypal/admin")

# Itterate over the directories array and run the process_directory function on each directory
for dir in "${directories[@]}"; do
	process_directory $dir
done
