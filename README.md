# MAGENTO 2 SUBSCRIPTION INTEGRATION BY NOVALNET
The <a href="https://www.novalnet.com/modul/magento-2-payment">Magento</a> 2 Payment Gateway by Novalnet enables secure integration of payments and payment services for all Magento 2 shops. The full-service platform allow merchant to <a href="https://www.novalnet.de/produkte"> automate payment processing</a>, enrich customer experiences and improve conversion rate through one interface and one contract partner.

## Features

### For Shop Admins
-	Create and manage subscription plans
-   Configure subscription plans to one or more product(s)
-	Manage customers’ subscription profiles
-	Manual subscription cancellation from the admin panel 
-	Enable or disable Trial Subscription payment mode (Trial Billing Amount, Trial Billing Cycle, and Initial Fee)

### For Customers
-	Customers can view and cancel their subscriptions from their dashboard

## Advanced Features for Shop Admin

### Create & Manage Subscription Plans
Novalnet’s Subscription extension for Magento 2 allows the Shop admin to create, suspend and cancel subscription plans for each product in the shop system
### Quick Subscription Configuration
Create new subscription plans almost instantly by configuring the required number of payments, payment frequency, and several specific pricing parameters
### Flexible Product Configuration
Customize billing intervals and billing cycles for each product. You can set an initial amount as a fee for a subscription plan, there is no restriction for the initial fee value. Set up a trial period for any subscription from the admin panel. This lets customers try a product for free until a certain period
### Multiple Subscription Plan
Shop admins can apply one or more subscription plans to a product. The product's plan can be set to be purchased as subscription only, one-time only, or in both variants.
### Mixed Shopping Cart
This extension makes shopping easy by allowing carts to have both subscription type and one-time purchase products.
### Email Alerts for Recurring Payments
Remind subscribers of the next payment by sending them email alerts. The alert includes the next billing reminder and subscription cancellation option.
### ‘Recurring Profiles’ Tab
Conveniently keep track of recurring orders and their details under the ‘Recurring Profiles’ tab.

For detailed documentation and other technical inquiries, please send us an email at <a href="mailto:sales@novalnet.de"> sales@novalnet.de </a>

## Installation via Composer

#### Follow the below steps and run each command from the shop root directory
 ##### 1. Run the below command to install the payment module
 ```
 composer require novalnet/magento2-subscription-module
 ```
 ##### 2. Run the below command to check subscription extension has been activated properly
 ```
 php bin/magento module:status
 ```
 ##### 3. Run the below command to enable the activated extension
 ```
 php bin/magento module:enable Novalnet_Subscription
 ```
 ##### 4. Run the below command to upgrade the extension
 ```
 php bin/magento setup:upgrade
 ```
 ##### 5. Run the below command to re-compile the Magento setup command
 ```
 php bin/magento setup:di:compile
 ```
 ##### 6. Run the below command to to deploy static-content files (images, CSS, templates and js files)
 ```
 php bin/magento setup:static-content:deploy -f
 ```
 
 ## Installation through Marketplace
 - Signup or login in the <a href="https://marketplace.magento.com/">Magento Marketplace </a>
 - Purchase the <a href="https://marketplace.magento.com/novalnet-module-payment.html"> Magento payment extension </a> by Novalnet for free
 - Upload the package content into the root directory
 - Upgrade, compile and deploy as explained in the section above

## Documentation & Support
For more information about the Magento 2 Subscription Integration by Novalnet, please get in touch with us: <a href="mailto:sales@novalnet.de"> sales@novalnet.de </a> or +49 89 9230683-20<br>

Novalnet AG<br>
Zahlungsinstitut (ZAG)<br>
Feringastr. 4<br>
85774 Unterföhring<br>
Deutschland<br>
Website: www.novalnet.de 

## Who is Novalnet AG?
<p>Novalnet AG is a <a href="https://www.novalnet.de/zahlungsinstitut"> leading financial service institution </a> offering payment gateways for processing online payments. Operating in the market as a full payment service provider Novalnet AG provides online merchants user-friendly payment integration with all major shop systems and self-programmed sites.</p> 
<p>Accept, manage and monitor payments all on one platform with one single contract!</p>
<p>Our SaaS engine is <a href="https://www.novalnet.de/pci-dss-zertifizierung"> PCI DSS </a> certified and designed to enable real-time risk management, secured payments via escrow accounts, efficient receivables management, dynamic member and subscription management, customized payment solutions for various business models (e.g. marketplaces, affiliate programs etc.) etc.</p>
