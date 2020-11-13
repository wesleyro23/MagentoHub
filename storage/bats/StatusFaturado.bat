@ECHO OFF
CLS
echo Entra na pasta MagentoHub - statusFaturado
cd\xampp\htdocs\MagentoHub
php artisan command:statusFaturado
exit
