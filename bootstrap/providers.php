<?php

return [
    App\Providers\AppServiceProvider::class,
    Modules\System\Providers\SystemServiceProvider::class,
    Modules\Core\Providers\CoreServiceProvider::class,
    Modules\Loyalty\Providers\LoyaltyServiceProvider::class,
    Modules\PurchaseOrder\Providers\PurchaseOrderServiceProvider::class,
    Modules\FnB\Providers\FnBServiceProvider::class,
    Modules\Accounting\Providers\AccountingServiceProvider::class,
];
