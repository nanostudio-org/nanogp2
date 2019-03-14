# nanogp2

PHP add-on for <b>[nanogallery2](https://github.com/nanostudio-org/nanogallery2)</b> for accessing **Google Photos** content.   
  
Based on the Google Photos API.  
  
&nbsp;    
&nbsp;    
#### USE OF THIS APP REQUIRES ADVANCED SKILLS :exclamation:
This app is delivered as-is, changes on Google side may break it without any warning.  

&nbsp;    
&nbsp;    
---
---
### :arrow_forward: Upgrading from NANOGP to NANOGP2
The API used by nanogp is depreciated by Google and will be turned off on **march 2019**.
A new API is available but the usage is not compatible with the depreciated one.

##### Migration - step by step:
1. Install nanogp2 in a new folder (do not install over nanogp)  
    - [see installation section](#arrow_forward-installation)  
2. Configure nanogp2  
    - new Google API authorizations are required
    - [see configuration section](#arrow_forward-configuration)
3. update nanogallery2 settings in your HTML pages:  
   - point google2URL to the new nanogp2 URL, e.g.: `'google2URL': 'https://YOUR_WEB_SERVER/nanogp2/nanogp2.php'`
   - album's IDs have changed, update the value of the `album` parameter

---
---
&nbsp;    
&nbsp;    

### :arrow_forward: Installation

##### :heavy_minus_sign: Pre-requisites:
- Web server with PHP version > 5.4+  
- Cannot be run on `localhost`  
- **nanogallery2 v2.4.1**


##### :heavy_minus_sign: Installation procedure  

- Create a folder named `nanogp2` on your web server.
- Copy the content of the `dist` folder in this folder.

---

&nbsp;    
&nbsp;    

### :arrow_forward: Enable Google API - Google API Console

1. For your nanogp2 installation, you need to register your instance using the <b>[Google API Console](https://console.developers.google.com/)</b>.
    - create a new project called **nanogallery2gp-YOUR-INSTANCE-NAME** (the project name should be unique, so replace YOUR-INSTANCE-NAME with the name of your own instance)
    - create a **OAuth consent screen**
      - application name: `nanogallery2gp-YOUR-INSTANCE-NAME`
      - set the support email
      - add scopes: `email`, `profile`, `openid`
      - authorized domains: name of the domain where your nanogp2 is installed
    - create credentials kind **OAuth Client ID**
      - application type: `Web application`
      - name: `nanogallery2gp-YOUR-INSTANCE-NAME`
      - Authorized redirect URIs: set the full path to your `authorize.php` (once with `http`, and once with `https`)
2. Google then provides information you'll need later, such as a **client ID** and a **client secret**.
3. Activate the **Google Photos Library API** in  the **Google API Console**. (If the API isn't listed in the API Console, then skip this step).

---

&nbsp;    
&nbsp;    

### :arrow_forward: Configuration


Settings are defined in `admin/config.php`:
  
```
  $cfg_client_id     = 'yyy';
  $cfg_client_secret = 'zzz';
  $albums_filter     = ['sauvegarde', 'backup'];
```
  
**Client ID** (`$cfg_client_id`) and **client secret** (`$cfg_client_secret`) can be obtained from the <b>[Google API Console](https://console.developers.google.com/)</b>.  
`$albums_filter` is used to filter albums out. Albums with a title containing one of the string will not be displayed.
    
  
:heavy_exclamation_mark: **Client secret should never be shared** :heavy_exclamation_mark: . Only your nanogp2 installation should access it.  
  
  
---

&nbsp;    
&nbsp;    

### :arrow_forward: User authorization


&nbsp;    
#### :heavy_minus_sign: Grant authorization

1. Once the settings are defined, you need to grant authorization to nanogp2 to access your Google Photos account.  
2. Use a browser and open the `authorize.php` page: `https://YOUR_WEB_SERVER/nanogp2/authorize.php`  
3. Google displays a consent screen, asking you to authorize your instance of nanogp2 to request some of your data.
If you get a warning message `This app isn't verified`, you need to display the advanced options to grant authorization to your nanogp2 instance.  

At the end of the process, your **user-ID** is displayed. This value should be set in your **nanogallery2's options** (`userID`).  
  
(if you've granted authorization and if you want to grant authorization again, follow the steps from the section `Manually revoke authorization`).
  
  
&nbsp;    
#### :heavy_minus_sign: nanogallery2 parameters

After authorization is granted, from your browser, open the `authorize.php` page again to display the parameters for nanogallery2.

&nbsp;    
#### :heavy_minus_sign: Security  

The `admin` folder should only be accessible to your PHP applications, and not from a browser.  
For this, you may for example put an `.htaccess` file containing `deny from all`.

&nbsp;    
#### :heavy_minus_sign: Manually revoke authorization  
- delete the folder corresponding to the user in `admin/users`
- delete the authorization to your instance of `nanogp2`: https://myaccount.google.com/permissions

---

&nbsp;    
&nbsp;    

### :arrow_forward: CONFIDENTIALITY

All your photos albums can by accessed by nanogp2. This may be misused by malicious people.  
Please use **nanogp2 only with a Google Photos account which does not contain any personal or privat data**.  
  
- USE A DEDICATED GOOGLE PHOTOS ACCOUNT
- configure the option `$albums_filter` to protect your privacy

---

&nbsp;    
&nbsp;    

### :arrow_forward: Retrieve one user's list of albums

Command to generate a report with the list of album's names and IDs for one specific user:  
`https://YOUR_WEB_SERVER/nanogp2/nanogp2.php?nguserid=USER_ID&report`  
  
Replace `YOUR_WEB_SERVER` and `USER_ID` with the correct values for your nanogp2 instance.

For security reason, the report is generated in the user's folder on the server (`nanogp2/admin/users/USER_ID/content.txt`).
