Instamojo-PrestaShop v 0.0.2
====
----
This module allows us to use [Instamojo](https://www.instamojo.com) as Payment Gateway in PrestaShop websites.

###Installation
---
- Download the [zip file](https://github.com/ashwch/Instamojo-PrestaShop/archive/master.zip) and unpack its files into a folder named **instamojo** into the modules folder of your PrestaShop installation. You will have to create the folder **instamojo** if not present already. So, after extraction `modules/instamojo` will look like:

    ```
    $ tree instamojo/
    instamojo/
    ├── config.xml
    ├── instamojo-api.php
    ├── instamojo.php
    ├── js
    │   └── jquery.js
    ├── logo.png
    ├── readme.md
    ├── validation.php
    └── views
        └── templates
            ├── admin
            └── front
                └── instamojo.tpl

    5 directories, 8 files
    ```

      **Note that** it shouldn't be `/modules/instamojo/Instamojo-PrestaShop/instamojo.php`. but ` /modules/instamojo/instamojo.php`.

- Now go the admin backend and look for "instamojo" in modules, there click on install and that will install the plugin.

### Creating a Payment Link
----
In this section we will learn how to create a Payment link along with how to get the required values for `Payment Link` and `Custom Field` .

- Create a Payment Link on Instamojo under the **Services/Membership** option.

  Set the price to Rs. 10 and enable **"Pay what you want"**.  Under **Title** and **Description**, you may enter something that describes your business and the nature of the products being sold.

  Under **Advanced settings** of the same link there's a field **Custom Redirection URL**, here if your website's url is **http://www.example.com** then use **http://www.example.com/modules/instamojo/validation.php** as Custom Redirection URL.

 Now click on **Get started** to save the button.
 
- Now copy the Payment Link URL and paste this in **Payment Link** field. URL's format is usually: **https://www.instamojo.com/<username>/<slug>/**
- Now on the Payment Link page go to **More options** and click on **Custom Fields**
 Create a custom field called **Order ID** and mark it as **required**. In the custom field creation page, hover over the field you just created. You'll see a field with the format **Field_**. Note down the full name (including the **Field_** bit. Note that this is case sensitive!). Enter this name in the **Custom field** field of the Instamojo module configuration page in PrestaShop.

### Auth
---
In this section we will learn how to get the values of fields  `API Key`,  `Auth token` and `Private salt`.

Go the [Instamojo developers](https://www.instamojo.com/developers/) page, if your are not logged in already then login first and then you'll see the value of `API Key`,  `Auth token`,  `Private salt` there on the bottom left side of the page.

Simply copy and paste their values in the configuration form in their respective fields.

Now simply click on **save** to save these setting and now the **Checkout with Instamojo**<sup>*</sup> button will show up on the checkout page

### Checkout button label

This can be anything you want to display to the user on the checkout page, default value is **"Pay using Instamojo"**. You can change it to anything like **"Pay using DB/CC/Net Banking"**(ignore the quotes).


----

What's new in v 0.0.2
----

- Fixed sorting issue for PHP versions older than 5.4.0.
- Replaced **Payment Button HTML** option from the configuration with simple **Payment Link** option.
- Added **Checkout button label** option for custom showing custom button label to users during checkout.
- Added a logger to track errors related to the module, it can be found under **log** directory of the PrestaShop installation directory by the name of **imojo.log**.
