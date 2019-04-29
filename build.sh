#!/usr/bin/env bash
CWD_BASENAME=${PWD##*/}

FILES+=("${CWD_BASENAME}.php")
FILES+=("logo.png")

MODULE_VERSION="$(sed -ne "s/\\\$this->version *= *['\"]\([^'\"]*\)['\"] *;.*/\1/p" ${CWD_BASENAME}.php)"
MODULE_VERSION_FILE=`echo ${MODULE_VERSION} | sed -e "s/\./_/g"`;
MODULE_VERSION=${MODULE_VERSION//[[:space:]]}
ZIP_FILE="${CWD_BASENAME}-${MODULE_VERSION_FILE}.zip"

echo "Going to zip ${CWD_BASENAME} version ${MODULE_VERSION}"

cd ..
rm -f ${ZIP_FILE};

for E in "${FILES[@]}"; do
  find ${CWD_BASENAME}/${E}  -type f -exec zip -9 ${ZIP_FILE} {} \;
done

for E in `find ${CWD_BASENAME}/controllers -type f -name "*.php" -o -name "*.html" -o -name "*.txt"`; do
  zip -9 ${ZIP_FILE} $E;
done;

for E in `find ${CWD_BASENAME}/views -type f -name "*.php" -o -name "*.tpl" -o -name "*.css" -o -name "*.js"`; do
  zip -9 ${ZIP_FILE} $E;
done;
