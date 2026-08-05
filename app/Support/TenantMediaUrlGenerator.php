<?php

namespace App\Support;

use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class TenantMediaUrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        if (tenancy()->initialized) {
            $path = $this->getPathRelativeToRoot();

            return $this->versionUrl(tenant_asset($path));
        }

        // 中央后台使用默认逻辑
        return parent::getUrl();
    }
}
