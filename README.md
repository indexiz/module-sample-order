# Magento 2 - Sample Order Extension

This Magento 2 extension allows customers to easily order sample items through a dedicated checkout popup form. The extension provides an easy way for customers to request up to three different sample items with their shipping details and a simple "Request Sample" button.

This module was specifically designed for [Tiles Porcelain](https://tilesporcelain.co.uk/), allowing them to provide a seamless experience for customers to request free tile samples directly from their product pages.

## Key Features

- **Sample Tiles Button on Product Pages:** A "FREE CUT SAMPLE" button is displayed on every Product Detail Page (PDP), allowing customers to quickly add sample tile items to their cart.

- **Request Up to 3 Samples:** Customers can request up to 3 different tile samples in one order.

- **Dedicated Sample Order Form:** A separate checkout popup form is displayed when customers click the "FREE CUT SAMPLE" button on PDP, where they can fill in their shipping details.

- **Simple, User-Friendly Interface:** Easy-to-use and simplest checkout process.

- **Free Sample Requests:** Customers can order up to three sample items free of charge, making it easier for them to test products before purchasing.

## Installation

1. Download the extension files and upload them to the `/app/code` directory of your Magento installation.

2. Run the following commands to enable the extension and update the Magento database:

```bash
php bin/magento module:enable Indexiz_SampleOrder
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
