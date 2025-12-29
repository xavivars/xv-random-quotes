#! /bin/bash
# A modification Brent Shepherds modification of Dean Clatworthy's deploy script
# as found here: https://github.com/thenbrent/multisite-user-management/blob/master/deploy.sh
# The difference is that this script lives in the plugin's git repo & doesn't require an existing SVN repo.

# main config
PLUGINSLUG="xv-random-quotes"
MAINFILE="xv-random-quotes.php" # this should be the name of your main php file in the wordpress plugin


CURRENTDIR=`pwd`
git status $CURRENTDIR &>/dev/null
if [ $? -ne 0 ];
then
    CURRENTDIR=$(dirname `pwd`)
fi

# git config
GITPATH="$CURRENTDIR/" # this file should be in the base of your git repository

# svn config
SVNPATH="/tmp/$PLUGINSLUG" # path to a temp SVN repo. No trailing slash required and don't add trunk.
SVNURL="http://plugins.svn.wordpress.org/xv-random-quotes/" # Remote SVN repo on wordpress.org, with no trailing slash
SVNUSER="xavivars" # your svn username


# Let's begin...
echo ".........................................."
echo 
echo "Preparing to deploy wordpress plugin"
echo 
echo ".........................................."
echo 

# Check if subversion is installed before getting all worked up
if ! which svn >/dev/null; then
	echo "You'll need to install subversion before proceeding. Exiting....";
	exit 1;
fi

# Check version in readme.txt is the same as plugin file after translating both to unix line breaks to work around grep's failure to identify mac line breaks
NEWVERSION1=`grep "^Stable tag:" $GITPATH/readme.txt | awk -F' ' '{print $NF}'`
echo "readme.txt version: ${NEWVERSION1}"
NEWVERSION2=`grep "^Version:" $GITPATH/$MAINFILE | awk -F' ' '{print $NF}'`
echo "$MAINFILE version: ${NEWVERSION2}"

if [ "$NEWVERSION1" != "$NEWVERSION2" ]; 
then 
    echo "Version in readme.txt & $MAINFILE don't match. Exiting.... "; 
    echo "$NEWVERSION1 - $NEWVERSION2";
    exit 1; 
fi

echo "Versions match in readme.txt and $MAINFILE. Let's proceed..."

if git show-ref --tags --quiet --verify -- "refs/tags/$NEWVERSION1"
then 
	echo "Version $NEWVERSION1 already exists as git tag. Exiting...."; 
	exit 1; 
else
	echo "Git version does not exist. Let's proceed..."
fi

cd $GITPATH
echo -e "Enter a commit message for this new version: \c"
read COMMITMSG
git commit -am "$COMMITMSG"

echo "Tagging new version in git"
git tag -a "$NEWVERSION1" -m "Tagging version $NEWVERSION1"

echo "Pushing latest commit to origin, with tags"
git push origin main
git push origin main --tags

echo 
echo "Creating local copy of SVN repo ..."
svn co $SVNURL $SVNPATH

echo "Clearing svn repo so we can overwrite it"
rm -rf $SVNPATH/trunk/*

echo "Exporting the HEAD of main from git to the trunk of SVN"
git checkout-index -a -f --prefix=$SVNPATH/trunk/

echo "Moving assets to SVN root"
mv $SVNPATH/trunk/assets/* $SVNPATH/assets
rmdir $SVNPATH/trunk/assets

echo "Removing development files from SVN export"

# Remove development and build configuration
rm -rf $SVNPATH/trunk/node_modules
rm -f $SVNPATH/trunk/package.json
rm -f $SVNPATH/trunk/package-lock.json
rm -f $SVNPATH/trunk/webpack.config.js

# Remove test and development files
rm -rf $SVNPATH/trunk/tests
rm -rf $SVNPATH/trunk/bin
rm -f $SVNPATH/trunk/phpunit.xml
rm -f $SVNPATH/trunk/.travis.yml

# Remove Docker and development documentation
rm -f $SVNPATH/trunk/Dockerfile.cli
rm -f $SVNPATH/trunk/docker-compose.yml
rm -f $SVNPATH/trunk/Makefile
rm -f $SVNPATH/trunk/setup.sh
rm -f $SVNPATH/trunk/.dockerignore
rm -f $SVNPATH/trunk/composer.json
rm -f $SVNPATH/trunk/composer.lock
rm -rf $SVNPATH/trunk/data

# Remove development documentation (keep readme.txt)
rm -f $SVNPATH/trunk/BUILD.md
rm -f $SVNPATH/trunk/DOCKER_USAGE.md
rm -f $SVNPATH/trunk/QUICKSTART.md
rm -f $SVNPATH/trunk/README-TESTING.md
rm -f $SVNPATH/trunk/README.md
rm -f $SVNPATH/trunk/TASK-1-COMPLETE.md
rm -f $SVNPATH/trunk/TASKS-2-7-COMPLETE.md
rm -f $SVNPATH/trunk/TODO.md
rm -f $SVNPATH/trunk/NEW_ARCHITECTURE.md

# Remove git files
rm -f $SVNPATH/trunk/.gitignore

echo "Changing directory to SVN and committing to trunk"
cd $SVNPATH/trunk/
# Add all new files that are not set to be ignored
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs svn add

# Delete all files that do not exist in anymore
svn status | grep -v "^.[ \t]*\..*" | grep "^\!" | awk '{print $2}' | xargs svn del

svn commit --username=$SVNUSER -m "$COMMITMSG"


echo "Creating new SVN tag & committing it"
cd $SVNPATH
svn copy trunk/ tags/$NEWVERSION1/
cd $SVNPATH/tags/$NEWVERSION1
svn commit --username=$SVNUSER -m "Tagging version $NEWVERSION1"

echo "Removing temporary directory $SVNPATH"
rm -fr $SVNPATH/

echo "*** FIN ***"
