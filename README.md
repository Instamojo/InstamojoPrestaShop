# Instamojo Prestashop Payment Gateway Plugin

Tested on PrestaShop 1.5+

## Download:

Download `InstamojoPrestaShop.zip` file from the [latest release](https://github.com/Instamojo/InstamojoPrestaShop/releases/latest).

**Note**: Don't download the files under **Downloads** section. They won't work.

![Imgur](http://i.imgur.com/GWw6bU5.png)

## Installation

#### Automatic Installation

1. Go to "Modules and Services" from Main menu.
2. Click on "Add a new module".
3. Now click on zip file you had downloaded earlier and upload it.
4. Now search for Instamojo in your plugins and click on "Install" button corresponding to Instamojo module.

#### Manual Installation:

1. Extract the zip file in your in `modules` directory of your PrestaShop instllation directory.
4. Now search for Instamojo in your plugins and click on "Install" button corresponding to Instamojo module.

## Configuration

1. After installation click on "Configure" button corresponding to Instamojo module.
2. Fill the following details:

    -  **Checkout Label:** This is the label users will see during checkout, its default value is "Pay using Instamojo". You can change it to something more generic like "Pay using Credit/Debit Card or Online Banking".
     
    - **Client ID** and **Client Secret** - Client Secret And Client ID can be generated on the [Integrations page](https://www.instamojo.com/integrations/). Related support article: [How Do I Get My Client ID And Client Secret?](https://support.instamojo.com/hc/en-us/articles/212214265-How-do-I-get-my-Client-ID-and-Client-Secret-)
    
    - **Test Mode:** If enabled you can use our [Sandbox environment](https://test.instamojo.com) to test payments. Note that in this case you should use `Client Secret` and `Client ID` from the test account not production.

## Migrating from older version(version < 2.0.0)

If you were already using older version of our plugin then follow these steps:

1. Go to "Modules and Services" from Main menu.
2. Now search for Instamojo in your plugins and click on "Unnstall" button corresponding to Instamojo module and then click on "Delete" to remove it.
3. You might want to clear the cache by going to `Advanced Parameters` -> `Performance`.

## Support

For any issue send us an email to support@instamojo.com and share the `imojo.log` file. The location of `imojo.log` file is `log/imojo.log`.
 