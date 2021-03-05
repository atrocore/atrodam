![logo](_assets/AtroDAM_logo_color_250.png)

# What Is AtroDAM?

AtroDAM is an [open-source digital asset management system (DAM)](https://atrodam.com), developed by AtroCore UG (haftungsbeschränkt), which is based on the [AtroCore](https://github.com/atrocore/atrocore) software platform. AtroDAM (as well as AtroCore) is distributed under GPLv3 License and is free. It has a lot of features right out-of-the-box and thus is an excellent tool for managing media assets as well as different kinds of other digital assets and their derivatives.

AtroDAM is a single page application (SPA) with an API-centric and service-oriented architecture (SOA). It has a flexible data model based on entities and relations of all kinds among them.  AtroDAM allows you to gather, store, and organize all kinds of digital assets in one place.

![banner](_assets/atrodam-product-en.png)

## What Are the Advantages of AtroDAM?

- Many out-of-the-box features
- Free – 100% open source, licensed under GPLv3
- REST API
- Service-oriented architecture (SOA)
- Responsive and user friendly UI
- Configurable (entities, relations, layouts, labels, navigation, dashboards, etc.)
- Extensible with [modules](https://atrodam.com/product) 
- Includes advantages of [AtroCore](https://github.com/atrocore/atrocore).

## Features

AtroDAM comes with a lot of features directly out of the box, including:

- different types of media and other digital assets;
- private and public ownership of the digital assets;
- mass upload;
- advanced configuration and quality control;
- automatic check for duplicates;
- extracting of metadata information;
- taxonomies – asset categories and tagging;
- asset relations;
- content management;
- asset collections;
- automatic versioning and creation of renditions (additional [modules](https://atrodam.com/product) are needed); 
- and much more.

Want to learn more about the AtroDAM functions and its advantages for you? Please, visit our [website](https://atrodam.com/features)! 

## Technology

AtroCore is based on EspoCRM and uses PHP7, backbone.js, and Composer.

Want to know more about AtroDAM technology? Please, visit our [website](https://atrodam.com/features)!

## Integrations

AtroDAM has a REST API and can be integrated with any third-party system. The out-of-the-box AtroDAM is integrated with [AtroPIM](https://github.com/atrocore/atropim), which is our another open source application for Product Information Management.

Please, [ask](https://atrodam.com/contact), if you want to know more.

## Requirements

- Unix-based system. Ubuntu is recommended.
- PHP 7.1 or above.
- MySQL 5.5.3 or above.

## Configuration Instructions Based on Your Server

- [Apache server configuration](https://github.com/atrocore/atrocore-docs/blob/master/en/administration/apache-server-configuration.md)
- [Nginx server configuration](https://github.com/atrocore/atrocore-docs/blob/master/en/administration/nginx-server-configuration.md)

### Installation

> The Installation guide is based on **Ubuntu**. Of course, you can use any Unix-based system, but make sure that your OS supports the following commands.<br/>

To create your new AtroDAM application, first make sure you are using PHP 7.1 or above and have [Composer](https://getcomposer.org/download/) installed.

1. Create your new project by running one of the following commands.

   If you don't need the demo data, run:
   ```
   composer create-project atrocore/skeleton-dam-no-demo my-atrodam-project
   ```
   If you need the demo data, run:
    ```
   composer create-project atrocore/skeleton-dam my-atrodam-project
   ```
2. Change recursively the user and group ownership for project files: 
   ```
   chown -R webserver_user:webserver_user my-atrodam-project/
   ```
   >**webserver_user** – depends on your webserver and can be one of the following: www, www-data, apache, etc.   

3. Change the permissions for project files: 
   ```
    find . -type d -exec chmod 755 {} + && find . -type f -exec chmod 644 {} +;
    find data custom upload -type d -exec chmod 775 {} + && find data custom upload -type f -exec chmod 664 {} +
   ```

4. Configure the crontab as described below.

   4.1. Run the following command:
      ```
      crontab -e -u webserver_user
      ```
   4.2. Add the following configuration:
      ```
      * * * * * /usr/bin/php /var/www/my-atrodam-project/index.php cron
      ```      

5. Install AtroPIM following the installation wizard in the web interface. Go to http://YOUR_PROJECT/

## License

AtroDAM is published under the GNU GPLv3 [license](https://github.com/atrocore/atrodam/blob/master/LICENSE.txt).

## Support

- AtroDAM is developed and supported by AtroCore UG (haftungsbeschränkt).
- To contact us, please, visit [AtroDAM Website](http://atrodam.com/).
