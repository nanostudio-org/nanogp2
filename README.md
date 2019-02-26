# nanogp2

PHP add-on for <b>[nanogallery2](https://github.com/nanostudio-org/nanogallery2)</b> for accessing **Google Photos** content.   
  
Based on the Google Photos API.
  
  
# WORK IN PROGRESS

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
   - point google2URL to the new nanogp2 URL, e.g.: `'google2URL': 'https://YOUR_WEB_SERVER/nanogp/nanogp2.php'`
   - album's IDs have changed, update the value of the `album` parameter

---
---

### :arrow_forward: Installation

##### Pre-requisites:
Web server with PHP version > 5.2  
Cannot be run on `localhost` (workaround, use `http://lvh.me` instead)  


##### Installation procedure  

- Create a folder named `nanogp` on your web server.
- Copy the content of the `dist` folder in this folder.

---

### :arrow_forward: Enable Google API - Google API Console

1. For your nanogp2 installation, you need to register it using the <b>[Google API Console](https://console.developers.google.com/)</b>.
2. Google then provides information you'll need later, such as a client ID and a client secret.
3. Activate the Google Photos Library API in the Google API Console. (If the API isn't listed in the API Console, then skip this step.)

---

### :arrow_forward: Configuration


Settings are defined in `admin/config.php`:
  
```
  $cfg_client_id     = 'yyy';
  $cfg_client_secret = 'zzz';
  $albums_filter     = ['sauvegarde', 'backup'];
```
  
`$cfg_client_id` and `$cfg_client_secret` can be obtained from the <b>[Google API Console](https://console.developers.google.com/)</b>.  
`$albums_filter` is used to filter albums out. Albums with a title containing one of the string will not be displayed.
  
  
---

### :arrow_forward: Authorization



##### Grant authorization

1. Once the settings are defined, you need to grant authorization to nanogp2 to access your Google Photos account.  
2. Use a browser and open the `authorize.php` page: `http://your_webserver/nanogp/authorize.php`  
3. Google displays a consent screen, asking you to authorize your instance of nanogp2 to request some of your data.
  
(if you want to grant authorization again, follow steps from the section `Manually revoke authorization`).

##### Security  

The `admin` folder should only be accessible to your PHP applications.  
For example, with `deny from all` set in `.htaccess` file.

##### Manually revoke authorization  
- delete the folder corresponding to the user in `admin/users`
- delete nanogp2's authorization: https://myaccount.google.com/permissions

---

### :arrow_forward: Retrieve one user's list of albums

Command to generate a report with album's name and ID:

For security reason, the report is generated in the user's folder on the server.
