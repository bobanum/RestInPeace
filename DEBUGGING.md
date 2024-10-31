
# Memory Aid To debug RestInPeace or Any Composer Module

1. Create a new project
2. Clone the module somewhere outside the project
   ```bash 
   git clone https://github.com/bobanum/restinpeace ..
   ```
3. Create or update the `composer.json` file
   ```json
   {
       "repositories": [
           {
               "type": "path",
               "url": "../RestInPeace"
           }
       ],
       "minimum-stability": "dev",
       "require": {
           "bobanum/restinpeace": "*"
       }
   }
   ```
   The above code will tell composer to look for the module in the parent folder
4. Run `composer update`