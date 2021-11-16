#!/usr/bin/env bash
set -e

# Change for specific plugin
slug='vars'
name='Vars'

# Check for required programs COMPLETE IT
all_found=Y
for prog in git ; do
	if ! [ -x "$(command -v $prog)" ]; then
		echo "Error: required program '$prog' is not installed." >&2
		all_found=N
	fi
done
if [ $all_found = N ]; then
	exit 1
fi

phpfile="${slug}.php"

git status

# version=$(wp --allow-root eval '$v = get_plugin_data( "'${phpfile}'" ); echo $v["Version"];') UNCOMMENT

echo "Going to release      : v${version}"

read -n 1 -s -r -p "If OK, press any key to continue (CTRL-C to exit)."

echo

git archive -o "../${slug}-${version}.zip" --prefix ${slug}/ HEAD

# hub release create -d -a "../${slug}-${version}.zip" -m "${name} ${version}" "${version}" UNCOMMENT

# "../${slug}-${version}.zip" UNCOMMENT
