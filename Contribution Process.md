# Contribution Process  
  
## Introduction  
  
Welcome to the project contribution guide!  
  
This documentation is intended for the developers who will participate in this project, it will explain in details how to make changes to the project.  

It details the steps and best practices that all developers should follow to make changes to the project in a consistent, efficient, and high-quality manner.  
  
Before you begin, make sure you understand the basics of Git and collaborative development concepts.  
  
## Work Flow  
  
Firstly, to setup the project, you will have to follow the Read Me instructions in the project's GitHub repository [here](https://github.com/ErwanCarl/ToDo-n-Co-App).  
  
Use the following git command to clone project repository from GitHub :  
``` git clone https://github.com/ErwanCarl/ToDo-n-Co-App.git ```

To get started, create a new branch from your actual one like this :
```
 git checkout -b type/DEV-XXX
```

Where : 
* ```type``` is the generic term for the context of our branch as we will see below for further informations about the conventions.
* ```DEV-XXX``` is for the feature's name as 'chat-message' for the development of a chat messages system in the application for exemple. It will largely depend on what you're actually coding, on which subject you're working on.      
    
Make your modifications and test your changes locally to make sure they work as expected.  
  
Then commit and push your branch :  
```
 git push -u origin feature/DEV-XXX
```
  
Create a pull request on the Project GitHub and let the automated tests run thanks to continuous integration.  
Be sure to include a clear description of your changes.  
  
Then wait for the review of your Lead Dev to validate your work.  
Be sure to respond to comments and make any necessary adjustments if needed / requested.  
  
Finally, merge your branch into the main one then delete yours.  
  
After a merge, update your local copy of the main  branch :  
```git checkout main```  
```git pull origin main```  
  

## Conventions

The main production branch is ```main```, changes should only be directly merged to it in case of hotfixes when a critical production anomaly occurs.  
In most cases, changes should be merged into the current release branch.

New develoments usually follow a X week sprint schedule, with a release branch named release-x.x.x.  
At each sprint start, a new release branch is created.  
This branch usually named such as "release-x.x.x", where the "x.x.x" stands for the current version of the software.  
During the sprint, new features and fixes are rolled into this branch.  
  
If fixes are needed during the sprint and they are applied to the main branch, a merge is performed from the main branch to the release branch.  
This ensures that patches are included in the new release preparation process.  
  
Once the X weeks of the sprint are over and the release branch is deemed stable, it is merged into the main branch.  
This means that all features and fixes developed during the sprint are integrated into the main software release.  
  
After merging the release branch into main, you have an updated and improved version of the software.  
This version will then be tested, validated and deployed.  
  
Branches should follow the following naming conventions:
 - feature/DEV-XXX for a new feature : for each new feature or task will be created a new branch. The name of these branches can be based on the name of the feature or the task, for example: "feature/name-of-the-feature".  
 - fix/DEV-XXX for a fix : To fix bugs, specific branches can be created. Their name could be: "bugfix/name-feature-bug".  
 - hotfix-/DEV-XXX for a hotfix : If critical issues that require immediate resolution arise in the current release, emergency patch branches may be created. For example: "hotfix/anomaly-name".
  
These are the most common cases, but as you'll see, we will have also ```release/version-x.x```, ```refactoring/refacto-name``` or ```documentation/docs-update``` branchs which will be used.  
  
Commits should be named :  
```
 DEV-XXX: commit description
```
  
## Quality Process  
  
Several rules are important to keep the app at the highest quality :  
* Follow coding and formatting standards to maintain a consistent code base : follow PSR guidelines for code formatting, structuring, and styling. 
* Use clear code comments, meaningful variable names, and follow development best practices.  
* Include relevant comments in the code and update the documentation as needed.  
* Do regular code reviews to ensure code quality and consistency.  
* Make sure the tests pass before submitting a contribution.  
* Use automation tools for formatting and testing : it means that each modification must be accompanied by appropriate tests.  
* Provide clear and concise descriptions in PRs to facilitate review.  
  
Automatic unit and functional tests will run when you'll commit.  
  
When a new pull request is opened on GitHub or a new push is done, automatic tests will be launch and will need to finish running on GitHub before being able to merge, this is done to prevent code regressions.  
  
Obviously, you will have to write the unit and functional tests related to your code and launch them thanks to Php Unit to ensure everything works properly.  

Mandatory tools  
* Git  
* GitHub  
* Php CS Fixer  
* Php Stan 
* Php Unit 
* Symfony Insight  

Recommended Tools  
* Blackfire  
* SonarQube  
* PHP Mess Detector
  
## Conclusion  
  
By following this guide, you will contribute effectively and in a high quality to the project.  
Be sure to follow best practices, adhere to conventions, and maintain a smooth contribution process.  
  
Your work will contribute to maintaining a quality code and harmonious collaboration within the team.
  