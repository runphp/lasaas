<?php

namespace Database\Seeders;

use Crumbls\Layup\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * 4 个落地页（home/features/about/pricing）以 Layup widget 数据驱动。
 *
 * 文案取自替换前的 Livewire 组件（git 历史中的 ⚡home/features/about/pricing），
 * 通过自定义 landing-* widget（app/Layup/Widgets + components/layup 视图）
 * 还原原页面结构与样式。内容存于 content JSON，后台 Filament Pages 资源
 * 中可用 LayupBuilder 画布（Divi 风格）可视化拖拽编辑。
 *
 * 页面 path 与 routes/web.php 中 PageController 路由的 defaults('slug', ...)
 * 一一对应。Layup 内容为单语言，这里以中文为主。
 */
class LayupPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'home' => [
                'title' => 'Lasaas - Laravel 多租户 SaaS 平台',
                'rows' => $this->homeRows(),
            ],
            'features' => [
                'title' => 'Features - Lasaas',
                'rows' => $this->featuresRows(),
            ],
            'about' => [
                'title' => 'About - Lasaas',
                'rows' => $this->aboutRows(),
            ],
            'pricing' => [
                'title' => 'Pricing - Lasaas',
                'rows' => $this->pricingRows(),
            ],
        ];

        foreach ($pages as $slug => $data) {
            Page::updateOrCreate(
                ['slug' => $slug],
                [
                    'path' => $slug,
                    'title' => $data['title'],
                    'status' => Page::STATUS_PUBLISHED,
                    'content' => ['rows' => $data['rows']],
                ]
            );
        }
    }

    /* ------------------------------------------------------------------
     | Home
     |------------------------------------------------------------------ */

    protected function homeRows(): array
    {
        return [
            // Hero
            $this->row('row_hero', [
                $this->column('col_hero', 12, [
                    $this->widget('landing-hero', [
                        'badge' => '永久免费开源 · MIT 协议',
                        'heading_line1' => '一套代码',
                        'heading_line2' => '无限可能',
                        'description' => '基于 Laravel 生态的现代化多租户 SaaS 平台。独立数据库隔离、自定义域名、团队协作、RBAC 权限体系——从站群系统到 AI 知识库，开箱即用，极速交付。',
                        'button_primary_text' => '免费开始使用',
                        'button_primary_url' => '/register',
                        'button_secondary_text' => '查看定价',
                        'button_secondary_url' => '/pricing',
                        'button_ghost_text' => 'GitHub',
                        'button_ghost_url' => 'https://github.com/runphp/lasaas',
                    ]),
                ]),
            ]),

            // 核心架构
            $this->row('row_core_heading', [
                $this->column('col_core_heading', 12, [
                    $this->widget('landing-heading', [
                        'badge' => '核心架构',
                        'heading' => '多租户架构，数据物理隔离',
                        'description' => '每个租户拥有独立数据库和专属域名，从底层保障数据安全与隐私合规。',
                    ]),
                ]),
            ]),
            $this->row('row_core_cards', [
                $this->column('col_core_1', 3, [$this->card('🗄️', '独立数据库', '每个租户独立数据库，数据物理隔离，满足等保合规要求', 'blue')]),
                $this->column('col_core_2', 3, [$this->card('🌐', '独立域名', '每个租户可绑定专属域名，完全白标，客户无感知', 'green')]),
                $this->column('col_core_3', 3, [$this->card('👥', '团队管辖', '用户可以在多个团队间切换，每个团队独立管理自己的租户', 'purple')]),
                $this->column('col_core_4', 3, [$this->card('🧩', '模块开关', '中央管理平台可精细控制每个租户 App 可用的功能模块', 'amber')]),
            ]),

            // 强大特性
            $this->row('row_features_heading', [
                $this->column('col_features_heading', 12, [
                    $this->widget('landing-heading', [
                        'badge' => '强大特性',
                        'heading' => '开箱即用的全栈能力',
                        'description' => 'Filament 一行命令生成 CRUD，Filament Shield 零代码分配权限——从零到上线只需几天。',
                    ]),
                ]),
            ]),
            $this->row('row_features_cards', [
                $this->column('col_feat_1', 4, [$this->card('🎛️', 'Filament 管理面板', '强大的管理面板，统一管理用户、团队、租户、角色和权限，支持自定义扩展', 'amber')]),
                $this->column('col_feat_2', 4, [$this->card('⚡', 'Livewire + Flux UI', '零 JavaScript 实现全栈响应式交互，Flux 组件库开箱即用，开发效率数倍提升', 'rose')]),
                $this->column('col_feat_3', 4, [$this->card('🛡️', 'Filament Shield RBAC', '自动扫描资源、页面、小组件生成细粒度权限，后台点点鼠标即可完成角色分配', 'purple')]),
                $this->column('col_feat_4', 4, [$this->card('🤝', '团队协作', '创建团队、邀请成员、分配角色，团队之间数据隔离，权限管理内置', 'green')]),
                $this->column('col_feat_5', 4, [$this->card('🔐', '认证与安全', '双因素认证、Passkeys 无密码登录、CSRF/XSS/SQL 注入防护，全方位安全保障', 'rose')]),
                $this->column('col_feat_6', 4, [$this->card('🌍', '国际化', '内置中英文双语支持，轻松扩展更多语言', 'orange')]),
            ]),

            // 业务场景
            $this->row('row_scenarios_heading', [
                $this->column('col_scenarios_heading', 12, [
                    $this->widget('landing-heading', [
                        'badge' => '业务场景',
                        'heading' => '覆盖多种业务场景',
                        'description' => '多租户 + 独立数据库 + 独立域名的架构，一套代码服务成百上千个独立站点。',
                    ]),
                ]),
            ]),
            $this->row('row_scenarios_cards', [
                $this->column('col_scen_1', 3, [$this->card('🏗️', '站群系统', '几百个站点，一个 git pull 全部更新', 'blue')]),
                $this->column('col_scen_2', 3, [$this->card('🏢', '企业官网平台', '服务商模式：开发一次，卖给 N 个客户', 'indigo')]),
                $this->column('col_scen_3', 3, [$this->card('🛒', '多品牌电商', '品牌独立运营，数据安全隔离，集中管控', 'amber')]),
                $this->column('col_scen_4', 3, [$this->card('🎓', '多校区管理', '校区独立，集团统一，互不干扰', 'green')]),
                $this->column('col_scen_5', 3, [$this->card('🚀', 'SaaS 产品创业', '从 0 到上线只需几天，聚焦业务而非架构', 'purple')]),
                $this->column('col_scen_6', 3, [$this->card('🏘️', '物业/园区管理', '一套系统管 N 个小区，物业公司的最爱', 'rose')]),
                $this->column('col_scen_7', 3, [$this->card('📰', '内容矩阵', '一套 CMS 撑起整个内容帝国', 'teal')]),
                $this->column('col_scen_8', 3, [$this->card('⚙️', '行业软件定制', 'CRM、ERP 等按客户分别部署，代码统一维护', 'zinc')]),
            ]),

            // AI 赋能
            $this->row('row_ai_heading', [
                $this->column('col_ai_heading', 12, [
                    $this->widget('landing-heading', [
                        'badge' => 'AI 赋能',
                        'heading' => 'AI + 多租户的化学反应',
                        'description' => '每个租户的数据是私有的、需要隔离的，而 AI 的能力又是通用的——两者结合，催生高价值场景。',
                    ]),
                ]),
            ]),
            $this->row('row_ai_cards', [
                $this->column('col_ai_1', 4, [$this->card('🧠', 'RAG 知识库平台', '企业私有文档问答，知识库绝对隔离', 'blue')]),
                $this->column('col_ai_2', 4, [$this->card('💬', 'AI 客服机器人', '每个租户基于自身知识独立训练', 'green')]),
                $this->column('col_ai_3', 4, [$this->card('✍️', 'AI 内容工厂', '按品牌生成文案，tone&voice 独立配置', 'purple')]),
                $this->column('col_ai_4', 4, [$this->card('📊', 'AI 数据分析', '租户上传数据，AI 生成洞察和预测', 'amber')]),
                $this->column('col_ai_5', 4, [$this->card('🤖', 'AI Agent 工作流', '每个租户编排自己的 Agent 和工具链', 'rose')]),
                $this->column('col_ai_6', 4, [$this->card('💰', 'AI 智能记账', '财务数据自动分类对账，物理隔离', 'teal')]),
            ]),

            // 底部 CTA
            $this->row('row_bottom_cta', [
                $this->column('col_bottom_cta', 12, [
                    $this->widget('landing-cta', [
                        'heading' => '准备好构建你的 SaaS 了吗？',
                        'description' => '克隆项目，一条命令初始化，明天就能给你的客户演示。',
                        'button_primary_text' => '免费注册，立即开始',
                        'button_primary_url' => '/register',
                        'button_secondary_text' => '查看定价方案',
                        'button_secondary_url' => '/pricing',
                    ]),
                ]),
            ]),
        ];
    }

    /* ------------------------------------------------------------------
     | Features
     |------------------------------------------------------------------ */

    protected function featuresRows(): array
    {
        return [
            // Hero
            $this->row('row_hero', [
                $this->column('col_hero', 12, [
                    $this->widget('landing-hero', [
                        'badge' => '应用市场',
                        'heading_line1' => '像搭积木一样构建你的系统',
                        'description' => '一个开放的应用模块市场：按需挑选模块自由组合，付费模块收益归模块开发者。可定制新模块或二次开发现有模块，开源通用模块还能享受更优价格。',
                    ]),
                ]),
            ]),

            // 运作模式
            $this->row('row_how_heading', [
                $this->column('col_how_heading', 12, [
                    $this->widget('landing-heading', [
                        'badge' => '运作模式',
                        'heading' => '模块市场怎么玩',
                    ]),
                ]),
            ]),
            $this->row('row_how_steps', [
                $this->column('col_step_1', 2, [$this->step('🧩', '浏览模块', '在市场中挑选你需要的功能模块', 'blue')]),
                $this->column('col_step_2', 2, [$this->step('🛠️', '自由组合', '按需搭配，只装你真正要用的功能', 'violet')]),
                $this->column('col_step_3', 2, [$this->step('✨', '定制开发', '没有满意的？定制新模块或改造现有模块', 'amber')]),
                $this->column('col_step_4', 2, [$this->step('🌍', '开源回流', '定制的通用模块开源，享更优价格', 'emerald')]),
                $this->column('col_step_5', 2, [$this->step('📈', '持续繁荣', '模块越丰富，后续开发成本越低', 'rose')]),
                $this->column('col_step_6', 2, []),
            ]),

            // 生态共赢
            $this->row('row_roles_heading', [
                $this->column('col_roles_heading', 12, [
                    $this->widget('landing-heading', [
                        'badge' => '生态共赢',
                        'heading' => '三方受益，正向循环',
                    ]),
                ]),
            ]),
            $this->row('row_roles_cards', [
                $this->column('col_role_1', 4, [$this->card('🛒', '客户方', '按需选取模块，用多少装多少。需要个性化功能？可定制开发，通用模块还能通过开源回流获得费用优惠。前期投入，后期受益。', 'blue')]),
                $this->column('col_role_2', 4, [$this->card('👨‍💻', '模块开发者', '开发通用模块上架市场，每次被客户选用即获得收益。一次开发，持续收入。', 'purple')]),
                $this->column('col_role_3', 4, [$this->card('🌳', '市场生态', '每个开源回流的模块都在壮大公共资产池。模块越丰富，下一个项目的搭建成本就越低，形成正向循环。', 'green')]),
            ]),

            // 开源激励机制
            $this->row('row_opensource_heading', [
                $this->column('col_opensource_heading', 12, [
                    $this->widget('landing-heading', [
                        'badge' => '开源激励机制',
                        'heading' => '你开源，我降价',
                        'description' => '如果你定制的模块<strong>通用性强</strong>，愿意将其开源贡献到市场中，我会在开发费用上给予<strong>更优的价格</strong>。',
                    ]),
                ]),
            ]),
            $this->row('row_opensource_cards', [
                $this->column('col_os_1', 3, [$this->card('💰', '定制费更优惠', '边际成本更低', 'blue')]),
                $this->column('col_os_2', 3, [$this->card('🛡️', '更稳定安全', '模块经社区打磨后更稳定、更安全', 'green')]),
                $this->column('col_os_3', 3, [$this->card('🔁', '不重复造轮子', '后续二次开发时基础功能直接复用', 'purple')]),
                $this->column('col_os_4', 3, [$this->card('📉', '生态成本走低', '整个生态的开发成本持续走低', 'amber')]),
            ]),

            // CTA
            $this->row('row_cta', [
                $this->column('col_cta', 12, [
                    $this->widget('landing-cta', [
                        'heading' => '有想法？聊聊看',
                        'description' => '想搭建系统还是贡献模块？大胆说出你的想法。免费评估，认真对待每一个需求。',
                        'contact_label' => '联系我',
                        'contact_value' => '微信：runphp',
                    ]),
                ]),
            ]),
        ];
    }

    /* ------------------------------------------------------------------
     | About
     |------------------------------------------------------------------ */

    protected function aboutRows(): array
    {
        return [
            // Hero
            $this->row('row_hero', [
                $this->column('col_hero', 12, [
                    $this->widget('landing-hero', [
                        'badge' => 'SOHO 全栈开发',
                        'heading_line1' => 'PHP / Go / Java 现代化开发',
                        'description' => '多年后端开发经验，掌握多语言技术栈，代码规范、结构清晰、易维护。只接靠谱项目，诚信交付。',
                    ]),
                ]),
            ]),

            // 可接范围
            $this->row('row_services_heading', [
                $this->column('col_services_heading', 12, [
                    $this->widget('landing-heading', [
                        'badge' => '可接范围',
                        'heading' => '我能帮你做什么',
                    ]),
                ]),
            ]),
            $this->row('row_services_cards', [
                $this->column('col_svc_1', 4, [$this->card('🚀', '新项目开发', '网站、后台管理系统、API 接口、业务系统，从零到交付', 'blue')]),
                $this->column('col_svc_2', 4, [$this->card('🔧', '二次开发 / 维护', '老项目功能新增、Bug 修复、逻辑梳理，接手遗留代码', 'amber')]),
                $this->column('col_svc_3', 4, [$this->card('⬆️', '版本 / 框架升级', 'PHP 版本升级、框架迁移、代码重构，平稳过渡', 'purple')]),
                $this->column('col_svc_4', 4, [$this->card('⚡', '性能优化 / 安全加固', '慢 SQL 优化、缓存策略、安全漏洞修复、代码规范提升', 'green')]),
                $this->column('col_svc_5', 4, [$this->card('🔗', '接口 / 集成开发', '第三方 API 对接、支付集成、短信/邮件服务、数据同步', 'teal')]),
                $this->column('col_svc_6', 4, [$this->card('💡', '技术咨询', '架构评审、技术选型建议、代码 Review，少走弯路', 'rose')]),
            ]),

            // 技术栈
            $this->row('row_tech_heading', [
                $this->column('col_tech_heading', 12, [
                    $this->widget('landing-heading', [
                        'badge' => '技术栈',
                        'heading' => '按你指定，灵活适配',
                        'description' => '以下是我的常用技术栈，也可以按你的需求适配其他技术。',
                    ]),
                ]),
            ]),
            $this->row('row_tech_cards', [
                $this->column('col_tech_1', 3, [$this->card('🧩', '框架', 'Laravel、Filament、Livewire、Vue', 'blue')]),
                $this->column('col_tech_2', 3, [$this->card('💻', '语言 / 环境', 'PHP 8、Go、Java、Node.js、MySQL、Redis', 'green')]),
                $this->column('col_tech_3', 3, [$this->card('🧭', '方向', 'SaaS 平台、多租户系统、API 服务、后台管理系统', 'purple')]),
                $this->column('col_tech_4', 3, [$this->card('🗂️', '其他', 'Docker、CI/CD、性能调优、安全加固', 'amber')]),
            ]),

            // 合作流程
            $this->row('row_workflow_heading', [
                $this->column('col_workflow_heading', 12, [
                    $this->widget('landing-heading', [
                        'badge' => '合作流程',
                        'heading' => '如何合作',
                    ]),
                ]),
            ]),
            $this->row('row_workflow_steps', [
                $this->column('col_wf_1', 2, [$this->step('📋', '需求沟通', '明确目标、范围与交付时间', 'blue')]),
                $this->column('col_wf_2', 2, [$this->step('✍️', '方案确认', '技术方案、报价与排期确认', 'violet')]),
                $this->column('col_wf_3', 2, [$this->step('💻', '开发交付', '分阶段开发，及时同步进度', 'amber')]),
                $this->column('col_wf_4', 2, [$this->step('🧪', '测试验收', '联调测试，按验收标准交付', 'emerald')]),
                $this->column('col_wf_5', 2, [$this->step('💬', '售后支持', '交付后持续跟进与维护', 'rose')]),
                $this->column('col_wf_6', 2, []),
            ]),

            // CTA
            $this->row('row_cta', [
                $this->column('col_cta', 12, [
                    $this->widget('landing-cta', [
                        'heading' => '有项目？聊聊看',
                        'description' => '免费评估，认真对待每一个需求。',
                        'contact_label' => '联系我',
                        'contact_value' => '微信：runphp',
                    ]),
                ]),
            ]),
        ];
    }

    /* ------------------------------------------------------------------
     | Pricing
     |------------------------------------------------------------------ */

    protected function pricingRows(): array
    {
        return [
            $this->row('row_pricing', [
                $this->column('col_pricing', 12, [
                    $this->widget('landing-pricing', [
                        'heading' => 'Pricing Plans',
                        'description' => 'Choose the perfect plan for your needs',
                    ]),
                ]),
            ]),
        ];
    }

    /* ------------------------------------------------------------------
     | Helpers
     |------------------------------------------------------------------ */

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function card(string $icon, string $title, string $description, string $color): array
    {
        return $this->widget('landing-card', [
            'icon' => $icon,
            'title' => $title,
            'description' => $description,
            'color' => $color,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function step(string $emoji, string $title, string $description, string $color): array
    {
        return $this->widget('landing-step', [
            'emoji' => $emoji,
            'title' => $title,
            'description' => $description,
            'color' => $color,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<string, mixed>
     */
    protected function row(string $id, array $columns): array
    {
        return [
            'id' => $id,
            'settings' => ['gap' => 'gap-6'],
            'columns' => $columns,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $widgets
     * @return array<string, mixed>
     */
    protected function column(string $id, int $span, array $widgets): array
    {
        return [
            'id' => $id,
            'span' => ['sm' => 12, 'md' => $span, 'lg' => $span, 'xl' => $span],
            'settings' => ['padding' => 'p-4'],
            'widgets' => $widgets,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function widget(string $type, array $data): array
    {
        return [
            'id' => 'widget_'.$type.'_'.Str::random(6),
            'type' => $type,
            'data' => $data,
        ];
    }
}
