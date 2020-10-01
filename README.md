# What Is TreoDAM?

TreoDAM is an open-source digital asset management system (DAM), developed by [TreoLabs GmbH](https://treolabs.com/), which is based on the [TreoCore](https://github.com/treolabs/treocore) software platform. TreoDAM (as well as TreoCore) is distributed under GPLv3 License and is free. It has a lot of features right out-of-the-box and thus is an excellent tool for managing media assets as well as different kinds of other digital assets and their derivatives.

TreoDAM is a single page application (SPA) with an API-centric and service-oriented architecture (SOA). It has a flexible data model based on entities and relations of all kinds among them.  TreoDAM allows you to gather, store, and organize all kinds of digital assets in one place.

## What Are the Advantages of TreoDAM?

- Many out-of-the-box features
- Free – 100% open source, licensed under GPLv3
- REST API
- Service-oriented architecture (SOA)
- Responsive and user friendly UI
- Configurable (entities, relations, layouts, labels, navigation, dashboards, etc.)
- Extensible with [modules](https://treodam.com/product) 
- Includes advantages of [TreoCore](https://github.com/treolabs/treocore).

## Features

TreoDAM comes with a lot of features directly out of the box, including:

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
- automatic versioning and creation of renditions (additional [modules](https://treodam.com/product) are needed); 
- and much more.

Want to learn more about the TreoDAM functions and its advantages for you? Please, visit our [website](http://treodam.com/features)! 

## Technology

TreoDAM is based on EspoCRM and uses PHP7, backbone.js, and Composer.

Want to know more about TreoDAM technology? Please, visit our [website](https://treodam.com/features)!

## Integrations

TreoDAM has a REST API and can be integrated with any third-party system. The out-of-the-box TreoDAM is integrated with [TreoPIM](https://github.com/treolabs/treopim), which is our another open source application for Product Information Management.

Please, [ask](https://treodam.com/contact), if you want to know more.

## Requirements

- Unix-based system. Linux Mint is recommended.
- PHP 7.1 or above (with pdo_mysql, openssl, json, zip, gd, mbstring, xml, curl, exif extensions).
- MySQL 5.5.3 or above.

## Configuration Instructions Based on Your Server

- [Apache server configuration](https://github.com/treolabs/treocore/blob/master/docs/en/administration/apache-server-configuration.md)
- [Nginx server configuration](https://github.com/treolabs/treocore/blob/master/docs/en/administration/nginx-server-configuration.md)

### Installation

> The Installation guide is based on **Linux Mint OS**. Of course, you can use any Unix-based system, but make sure that your OS supports the following commands.<br/>

To create your new AtroDAM application, first make sure you are using PHP 7.1 or above and have [Composer](https://getcomposer.org/) installed.

1. Create your new project by running the following command:
   ```
   composer create-project atrocore/skeleton-dam my-atrodam-project
   ```
2. Change recursively the user and group ownership for project files: 
   ```
   chown -R webserver_user:webserver_user my-atrodam-project/
   ```
   >**webserver_user** – depends on your webserver and can be one of the following: www, www-data, apache, etc.

3. Configure the crontab as described below.

   3.1. Run the following command:
      ```
      crontab -e -u webserver_user
      ```
   3.2. Add the following configuration:
      ```
      * * * * * /usr/bin/php /var/www/my-atrodam-project/index.php cron
      ```      

4. Install AtroPIM following the installation wizard in the web interface. Go to http://YOUR_PROJECT/
## License

TreoDAM is published under the GNU GPLv3 [license](https://github.com/treolabs/treodam/blob/master/LICENSE.txt).

## Support

- TreoDAM is developed and supported by [TreoLabs GmbH](https://treolabs.com/).
- Feel free to join [our Community](https://community.treolabs.com/).
- To contact us, please, visit [TreoDAM Website](http://treodam.com/).
