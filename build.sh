#!/usr/bin/env bash

ARCHIVE_NAME='gravityscores'

if ! command -v 'npm' &> /dev/null
then
    echo "NPM is not installed. Please install npm and then restart this script."
    exit
fi

set -e

CURRENT_DIRECTORY=`realpath $(dirname $0)`

pushd $CURRENT_DIRECTORY
    
    VERSION=`git branch --show-current`
    
    npm install
    npm run build

    rm -f "./${ARCHIVE_NAME}.${VERSION}.zip"
    zip -r "${ARCHIVE_NAME}.${VERSION}.zip" . -x './node_modules/*'  './js/*' '*.git*' './README.md' './*.json' './*.sh' '*~*' './webpack*' '*.zip' '*.log' 'Vagrantfile' './*.config.js'

popd

echo "Gravity Scores was build '$CURRENT_DIRECTORY/${ARCHIVE_NAME}.${VERSION}.zip'"
