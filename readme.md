# [Easy Digital Downloads](https://easydigitaldownloads.com) (OPEN MINDS Fork)

# Process For Forking Edd
This readme serves to outline the process we will follow when updating our customized fork of Easy Digital Downloads (EDD). 

## Background
_OPEN MINDS_ has decided to fork (manage a customized copy of) EDD in order to add some filters that haven't been approved by the EDD team. In order to gain the security and performance benefits of updates from the core EDD plugin, OPEN MINDS must merge changes from the master EDD repo in our custom fork. The steps for doing so are outlined below.

### Updating Steps
1. Add a remote source called 'upstream' and set it EDD's master branch on their repo. (needs to be done once) `git remote add upstream https://github.com/easydigitaldownloads/easy-digital-downloads.git` 
2. Fetch all the branches of that remote into remote-tracking branches, such as upstream/master: ```git fetch upstream```
3. Make sure that you're on your master branch: `git checkout master`
4. Rewrite your master branch so that any commits of yours that aren't already in upstream/master are replayed on top of that other branch: `git rebase upstream/master`
