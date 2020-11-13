echo off
cls
echo "========Inicio Atualizacao de Itens Magento por SKU========"
echo "Acessa Pasta MagentoHub"
cd\xampp\htdocs\MagentoHub
echo "Executa Processos"
php artisan queue:listen
echo "========Fim Atualizacao de Itens Magento por SKU==========="