Instamojo-PrestaShop  
====
----
This module allows us to use [Instamojo](https://www.instamojo.com) as Payment Gateway in PrestaShop websites.

###Installation
---
- Download the [zip file](https://github.com/ashwch/Instamojo-PrestaShop/archive/master.zip) and unpack its files into a folder named **instamojo** into the modules folder of your PrestaShop installation. You will have to create the folder **instamojo** if not present already. So, after extraction `modules/instamojo` will looks like: 

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
In this section we will learn how to create a Payment link along with how to get the required values for `Payment button HTML` and `Custom Field` .

- Create a Payment Link on Instamojo under the **Services/Membership** option. Set the price to Rs. 10 and enable **"Pay what you want"**.  Under **Title** and **Description**, you may enter something that describes your business and the nature of the products being sold. Under **Advanced settings** of the same link there's a field **Custom Redirection URL**, here if your website's url is **http://www.example.com** then use **http://www.example.com/modules/instamojo/validation.php** as Custom Redirection URL.
Now click on **Get started** to save the button. 
(I**MPORTANT:** If you saved the module with some other name than **instamojo** then this link won't work, if that's the case then replace **instamojo** in url with the folder name you chose.)
- Now on Payment links's page go to **More options** and click on **Custom Fields**. Now create a custom field called **Order ID** and mark it as **required**. In the custom field creation page, hover over the field you just created. You'll see a field with the format **Field_**. Note down the full name (including the **Field_** bit. Note that this is case sensitive!). Enter this name in the **Custom field** field of the Instamojo module configuration page in PrestaShop.
- Now go back to the Payment link's page again and this time under **Custom Fields** click on **Payment Button**. You can change the styling and text of the button there if you want but leave "Choose Button Behavior" unchanged. After you are done with your changes the the url from the box on the right side and paste this in `Payment button HTML` field of the Instamojo module configuration page in PrestaShop.

###Auth
---
In this section we will learn how to get the values of fields  `API Key`,  `Auth token` and `Private salt`.

Go the [Instamojo developers](https://www.instamojo.com/developers/) page, if your are not logged in already then login first and then you'll see the value of `API Key`,  `Auth token`,  `Private salt` there on the bottom left side of the page. Simply copy and paste their values in the configuration form in their respective fields.

Now simply click on **save** to save these setting and now the **Checkout with Instamojo**<sup>*</sup> button will show up on the checkout page

---
<sub>* It may not be **Checkout with Instamojo** for you if you changed the button text on the **Payment Button** page.</sub>
