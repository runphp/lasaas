<?php

namespace Database\Seeders;

use App\Enums\BillingCycle;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            // ===== 月度套餐 =====
            [
                'name'           => 'Free',
                'slug'           => 'free-monthly',
                'description'    => '适合个人开发者和小型项目免费入门。',
                'badge'          => null,
                'price'          => 0,
                'original_price' => null,
                'billing_cycle'  => BillingCycle::Monthly,
                'features'       => [
                    'projects'          => 3,
                    'storage_gb'        => 1,
                    'team_members'      => 1,
                    'api_requests'      => 1000,
                    'support'           => 'community',
                    'custom_domain'     => false,
                    'analytics'         => false,
                    'priority_support'  => false,
                ],
                'sort_order' => 1,
                'is_featured' => false,
                'is_active'   => true,
            ],
            [
                'name'           => 'Starter',
                'slug'           => 'starter-monthly',
                'description'    => '适合初创团队和中小型项目，功能齐全。',
                'badge'          => null,
                'price'          => 29.00,
                'original_price' => null,
                'billing_cycle'  => BillingCycle::Monthly,
                'features'       => [
                    'projects'          => 10,
                    'storage_gb'        => 10,
                    'team_members'      => 5,
                    'api_requests'      => 10000,
                    'support'           => 'email',
                    'custom_domain'     => true,
                    'analytics'         => false,
                    'priority_support'  => false,
                ],
                'sort_order' => 2,
                'is_featured' => false,
                'is_active'   => true,
            ],
            [
                'name'           => 'Pro',
                'slug'           => 'pro-monthly',
                'description'    => '适合专业团队和成长型企业，提供高级功能。',
                'badge'          => '推荐',
                'price'          => 99.00,
                'original_price' => 129.00,
                'billing_cycle'  => BillingCycle::Monthly,
                'features'       => [
                    'projects'          => 50,
                    'storage_gb'        => 100,
                    'team_members'      => 20,
                    'api_requests'      => 100000,
                    'support'           => 'priority',
                    'custom_domain'     => true,
                    'analytics'         => true,
                    'priority_support'  => true,
                ],
                'sort_order' => 3,
                'is_featured' => true,
                'is_active'   => true,
            ],
            [
                'name'           => 'Enterprise',
                'slug'           => 'enterprise-monthly',
                'description'    => '适合大型组织和企业级部署，无限制访问所有功能。',
                'badge'          => null,
                'price'          => 299.00,
                'original_price' => null,
                'billing_cycle'  => BillingCycle::Monthly,
                'features'       => [
                    'projects'          => -1,  // 无限制
                    'storage_gb'        => 1000,
                    'team_members'      => -1,  // 无限制
                    'api_requests'      => -1,  // 无限制
                    'support'           => 'dedicated',
                    'custom_domain'     => true,
                    'analytics'         => true,
                    'priority_support'  => true,
                ],
                'sort_order' => 4,
                'is_featured' => false,
                'is_active'   => true,
            ],

            // ===== 年度套餐 =====
            [
                'name'           => 'Free',
                'slug'           => 'free-yearly',
                'description'    => '适合个人开发者和小型项目免费入门，年度订阅。',
                'badge'          => null,
                'price'          => 0,
                'original_price' => null,
                'billing_cycle'  => BillingCycle::Yearly,
                'features'       => [
                    'projects'          => 3,
                    'storage_gb'        => 1,
                    'team_members'      => 1,
                    'api_requests'      => 1000,
                    'support'           => 'community',
                    'custom_domain'     => false,
                    'analytics'         => false,
                    'priority_support'  => false,
                ],
                'sort_order' => 1,
                'is_featured' => false,
                'is_active'   => true,
            ],
            [
                'name'           => 'Starter',
                'slug'           => 'starter-yearly',
                'description'    => '适合初创团队和中小型项目，年度订阅享8折优惠。',
                'badge'          => '8折优惠',
                'price'          => 279.00,
                'original_price' => 348.00,
                'billing_cycle'  => BillingCycle::Yearly,
                'features'       => [
                    'projects'          => 10,
                    'storage_gb'        => 10,
                    'team_members'      => 5,
                    'api_requests'      => 10000,
                    'support'           => 'email',
                    'custom_domain'     => true,
                    'analytics'         => false,
                    'priority_support'  => false,
                ],
                'sort_order' => 2,
                'is_featured' => false,
                'is_active'   => true,
            ],
            [
                'name'           => 'Pro',
                'slug'           => 'pro-yearly',
                'description'    => '适合专业团队和成长型企业，年度订阅享8折优惠。',
                'badge'          => '推荐',
                'price'          => 959.00,
                'original_price' => 1188.00,
                'billing_cycle'  => BillingCycle::Yearly,
                'features'       => [
                    'projects'          => 50,
                    'storage_gb'        => 100,
                    'team_members'      => 20,
                    'api_requests'      => 100000,
                    'support'           => 'priority',
                    'custom_domain'     => true,
                    'analytics'         => true,
                    'priority_support'  => true,
                ],
                'sort_order' => 3,
                'is_featured' => true,
                'is_active'   => true,
            ],
            [
                'name'           => 'Enterprise',
                'slug'           => 'enterprise-yearly',
                'description'    => '适合大型组织和企业级部署，年度订阅享8折优惠。',
                'badge'          => null,
                'price'          => 2879.00,
                'original_price' => 3588.00,
                'billing_cycle'  => BillingCycle::Yearly,
                'features'       => [
                    'projects'          => -1,
                    'storage_gb'        => 1000,
                    'team_members'      => -1,
                    'api_requests'      => -1,
                    'support'           => 'dedicated',
                    'custom_domain'     => true,
                    'analytics'         => true,
                    'priority_support'  => true,
                ],
                'sort_order' => 4,
                'is_featured' => false,
                'is_active'   => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->create($plan);
        }
    }
}
