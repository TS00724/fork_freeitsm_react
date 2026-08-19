# FreeITSM → React/EUI 迁移与 SOC 子系统主计划

版本：1.0  
基准日期：2026-08-19  
目标仓库：https://github.com/TS00724/fork_freeitsm_react  
执行方式：GPT Pro 分工期持续实施 + 用户在关键架构门参与代码阅读、决策和微调  
硬约束：禁止使用 GitHub Actions

## 1. 结论：18 个阶段，37 个 GPT Pro 工期

完整迁移建议拆成 **18 个阶段（P00–P17）**，进一步拆成 **37 个可独立恢复上下文的 GPT Pro 工期（WP-01–WP-37）**，并设置 **7 个强制人工审核门（G1–G7）**。

这里的“工期”不是自然日，而是一次独立的 GPT Pro 工作上下文。每个工期只允许处理一个平台层或一个模块纵向切片，结束时必须留下可由下一次上下文直接读取的交接包。

本计划故意不把工作压缩成少数大阶段。当前仓库约有 2,021 个文件、1,657 个 PHP 文件、167 个原生 JavaScript 文件、约 719 个内部 PHP API，以及 22 个主要模块。Tickets、BFF、安全/租户/RBAC、System Admin 和高交互编辑器均足以独占一次甚至多次长上下文。

> GitHub main 在本计划复核时仍未看到 frontend/、React、Vite 或 package manifest。若第一次搭建存在于本地、其他分支或尚未推送的工作树，WP-01 必须先识别并保护它，不能重复覆盖。

## 2. 三条项目路线

### 当前执行路线

1. 隔离 PHP 与 React/EUI。
2. 由用户参与复审 React 文件框架。
3. 复审通过后建立同源 Session + CSRF 的 UI BFF。
4. 按模块逐渐迁移 UI 与内部 API。
5. 完成全系统安全、兼容、性能、部署和旧 UI 退役。
6. 让 ITSM 具备作为 SOC 系统子系统的稳定接口与运维边界。

### 当前只做架构准备、不执行的路线

未来可能逐渐把后端能力迁移到 Go / go-zero，并形成集群化服务。本轮：

- 不写 Go 代码；
- 不引入 go-zero；
- 不拆微服务；
- 不引入 Kubernetes、服务注册、分布式事务或消息集群；
- 不为了未来设想破坏当前 PHP/MySQL 的可交付性。

本轮只通过清晰 BFF 契约、共享 Service、领域事件目录、显式租户/身份上下文、幂等性、可观测性和无状态化原则，避免给未来迁移制造新的耦合。

### SOC 子系统定位

当前应把边界写清而不提前实现整个 SOC：

- SOC 主系统负责告警、检测、调查编排和全局态势；
- ITSM 子系统负责工单、人员/团队、资产、CMDB、变更、问题、知识、SLA 和审计；
- 两者未来通过版本化 API/事件契约交互；
- ITSM 不直接依赖 SOC 数据库；SOC 也不直接写 ITSM 表；
- 所有跨系统请求携带 correlation ID、actor/tenant、来源、幂等键和 UTC 时间；
- 先定义事件目录和映射，例如 soc.alert.promoted → itsm.ticket.created、工单状态回传、资产/CI 富化，但本轮不部署消息代理。

## 3. 上下文不丢失机制

每次 GPT Pro 开始工作时，必须按顺序读取：

1. 本文件；
2. WORK_PROGRESS.md 与最新进度表；
3. VERIFICATION_REPORT.md；
4. docs/react-migration/DECISION_LOG.md；
5. 最新一个 docs/react-migration/handoffs/WP-XX.md；
6. 本工期相关 ADR 和模块 inventory；
7. git status --short、当前分支、最近提交和未提交 diff。

每个工期结束前必须新建 docs/react-migration/handoffs/WP-XX.md，内容固定为：

- 起始/结束 commit SHA 与分支；
- 本期目标和明确非目标；
- 已读取的关键文件；
- 修改文件清单；
- 新增/修改 UI route、BFF route、schema 和数据库迁移；
- 做出的架构决定及 ADR；
- 实际运行的验证命令与结果；
- 未完成项、blocker 和风险；
- 下一工期首先应读取的 5–10 个文件；
- 是否有信心 100% 完成：Yes/No；若 No，缺什么证据；
- 确认没有创建或依赖 GitHub Actions。

单个工期超过以下任一条件时必须拆分，不得继续扩大范围：

- 超过一个主要模块或超过 8 个用户流程；
- 同时修改认证、租户和多个业务模块；
- 超过一个数据库 schema migration；
- 无法在同一次上下文内完成 typecheck、相关 PHP 检查、契约测试和关键 E2E；
- 需要在没有用户确认的情况下改变 URL、认证、权限、BFF 契约或目录架构；
- 修改范围已经无法用一份清晰 handoff 完整描述。

## 4. 18 阶段总计划

| 阶段 | 工期 | 权重 | 主要内容 | 用户参与/退出条件 |
|---|---:|---:|---|---|
| P00 基线与项目控制 | 1 | 2% | 保护工作树、复核文件/API/路由、建立 inventory、route map、decision log、progress 与 handoff 模板 | 确认现有首次搭建在哪里；基线文件齐全 |
| P01 隔离式 React/EUI 首次搭建 | 1 | 3% | frontend/、React 18、TS strict、EUI、Vite、测试骨架、空 AppShell；不写 BFF | **G1：完成后强制停止，由用户检查整个 React 文件框架** |
| P02 用户参与的架构复审 | 1 | 3% | 逐文件讲解入口、providers、router、API client 占位、feature 结构、测试与构建；记录修改意见 | 用户签署目录、路由、状态管理、构建输出与 BFF 方针 |
| P03 BFF 契约与安全底座 | 2 | 9% | /api/ui/v1 front controller、统一响应、OpenAPI/TS 契约、session/bootstrap、login/logout、CSRF、tenant/RBAC/service context | **G2：用户检查 BFF 路由、安全边界与代码可读性** |
| P04 React 平台公共能力 | 2 | 7% | AppShell、导航、错误边界、theme、i18n、timezone、permissions、tenant switch、notifications、search、文件/流适配 | 公共能力有测试且旧 PHP 页面仍可 fallback |
| P05 Pilot 纵向切片 | 1 | 4% | Watchtower（含设置）完整 React/EUI + BFF 迁移，验证读写、权限、租户、主题、i18n | **G3：用户实际演示并决定后续模块 UI 模式** |
| P06 Tickets 全量迁移 | 4 | 13% | 列表/筛选/详情/thread；创建/回复/附件/notes；分配/批量/SLA/snooze/presence；merge/split/triage/dashboard/settings | **G4：核心工单业务逐项 parity 审核** |
| P07 Assets | 2 | 6% | inventory/detail/custody/users/tickets/labels/scan；dashboard/settings/vCenter/Intune 衔接 | 资产与租户隔离、文件和二维码流程通过 |
| P08 Knowledge + Tasks | 2 | 6% | 知识编辑/检索/AI/review/audience；任务 board/list/calendar/timeline/settings | 两模块分别完成 DoD |
| P09 Change + Problem | 2 | 5% | Change/CAB/审批/附件/calendar/settings；Problem/RCA/links/notes/settings | ITIL 关系与审计链验证 |
| P10 CMDB + Network/Process Mapper | 3 | 7% | CMDB class/object/relationship/impact；Network Mapper；Process Mapper | 图形编辑器可保存/恢复，复杂组件有 adapter |
| P11 Contracts + Documents + RFP | 2 | 6% | supplier/contract/term/payment；统一 documents；RFP 上传/抽取/合并/评分/AI | 文件权限、版本与敏感数据检查 |
| P12 运营模块 | 2 | 5% | Calendar、Morning Checks、Service Status、Software、Reporting/Intune | feed、图表、状态历史和 drill-down 验证 |
| P13 Workflow + Forms + LMS | 3 | 7% | workflow editor/execution/settings；form builder/submission/approval；course/SCORM/progress | 高交互编辑器分包验证，不强迫纯 EUI |
| P14 Messaging + Integrations + War Room | 2 | 4% | channel/template/send/AI；Jira/Azure DevOps/Slack 等；War Room | 外部服务缺凭据时明确 Blocked，不伪造 |
| P15 System Admin | 2 | 4% | analysts/teams/roles/capabilities/companies/modules/preferences；SSO/LDAP/security/API keys/webhooks/search/db verify/debug | **G5：用户重点审查权限、秘密与破坏性操作** |
| P16 Self-Service 与附属表面 | 2 | 3% | Self-Service 独立身份域；Webchat、System Wiki、browser extension 适配 | **G6：分别验证 analyst 与 self-service session 边界** |
| P17 系统硬化、SOC 就绪与切换 | 3 | 6% | 全局 parity/security/a11y/i18n/performance；SOC 子系统契约/可观测性；路由切换、rollback、旧 UI/API 退役、最终报告 | **G7：用户最终验收；Go-zero 仍不实施** |

总权重：100%。

## 5. 37 个 GPT Pro 工期

详细范围、交付物、验证要求、用户参与和状态字段记录在配套 Excel 的“GPT Pro工期”工作表。工期编号如下：

- WP-01：基线、inventory、route/API matrix 与项目控制文件；
- WP-02：隔离式 React/EUI 首次搭建；
- WP-03：用户参与的文件框架 walkthrough 与架构签署；
- WP-04–05：BFF 契约、安全、session/CSRF、tenant/RBAC；
- WP-06–07：React 平台公共能力；
- WP-08：Watchtower Pilot；
- WP-09–12：Tickets 四个纵向切片；
- WP-13–14：Assets 两个切片；
- WP-15：Knowledge；
- WP-16：Tasks；
- WP-17：Change；
- WP-18：Problem；
- WP-19：CMDB；
- WP-20：Network Mapper；
- WP-21：Process Mapper；
- WP-22：Contracts + Documents；
- WP-23：RFP Builder；
- WP-24：Calendar + Morning Checks；
- WP-25：Service Status + Software + Reporting/Intune；
- WP-26：Workflow；
- WP-27：Forms；
- WP-28：LMS；
- WP-29：Messaging + Integrations；
- WP-30：War Room；
- WP-31–32：System Admin 两个切片；
- WP-33：Self-Service；
- WP-34：Webchat + System Wiki + browser extension；
- WP-35：全系统 parity/security/a11y/i18n/performance；
- WP-36：SOC 子系统边界、observability 与未来 Go-zero 迁移说明（只写契约，不实施 Go）；
- WP-37：cutover、rollback、legacy retirement 与最终验收。

## 6. WP-02 后用户应该怎样阅读代码

WP-02 必须生成 docs/react-migration/CODE_READING_GUIDE.md。建议用户按照下列顺序阅读：

1. frontend/package.json、lockfile、vite.config.ts、tsconfig；
2. frontend/src/main.tsx；
3. frontend/src/app/App.tsx、providers.tsx、router.tsx；
4. frontend/src/config/ 与运行时 BASE_URL；
5. frontend/src/api/client.ts、错误类型和暂时为空的 contract 层；
6. frontend/src/auth/、permissions/、tenants/；
7. frontend/src/shell/ 与 EUI theme/provider；
8. frontend/src/features/module 的 route、page、component、api、types、tests；
9. frontend/src/test/ 和端到端测试入口；
10. build output 如何被极薄的 PHP shell 或静态服务器加载。

建议目录边界：

~~~text
frontend/
  package.json
  vite.config.ts
  tsconfig*.json
  src/
    main.tsx
    app/                 # providers、router、全局错误边界
    shell/               # EUI 导航和页面壳
    api/                 # typed client、errors、generated contracts
    auth/
    config/
    i18n/
    theme/
    permissions/
    tenants/
    components/          # 仅跨模块共享组件
    features/
      module/
        api/
        components/
        pages/
        routes.tsx
        types.ts
        tests/
    test/
~~~

PHP/BFF 目标边界：

~~~text
api/ui/v1/
  index.php              # 单一 front controller
  .htaccess
  lib/
    bootstrap.php
    routes.php
    response.php
    request.php
    session.php
    csrf.php
    openapi.php
  resources/
    bootstrap.php
    auth.php
    ...
includes/services/       # 共享业务逻辑，legacy/UI v1/public v1 共用
docs/react-migration/
~~~

隔离规则：

- TSX/TS 不包含 PHP；
- React 业务源代码不放进旧 PHP module 目录；
- BFF 不返回 HTML 业务片段；
- React 不直接读数据库；
- PHP 只允许保留极薄的 SPA host/runtime config；
- Node 仅用于 build/test，不作为生产后端；
- 在用户完成 G1 架构复审前，不开始大规模 BFF 或模块迁移。

## 7. 七个用户审核门

### G1：首次搭建后

用户读取完整 frontend/ 文件框架，重点决定目录可读性、feature 组织、Router/子目录、EUI theme、数据与 local state 边界、build output、PHP 隔离和测试放置。

### G2：BFF 底座后

用户检查 front controller、route table、session/bootstrap、CSRF、tenant/RBAC/object scope、error envelope、OpenAPI → TS 类型和 legacy API 退役方法。

### G3：Pilot 后

用户实际操作 Watchtower，确定卡片、设置页、Flyout、错误/空状态和移动端模式是否作为其余模块范式。

### G4：Tickets 后

用核心业务 parity checklist 逐项审查；Tickets 未过门，后续模块不能被标记为“整体架构已稳定”。

### G5：System Admin 后

重点检查角色、capability、租户、SSO、API Key、webhook、DB/debug 工具，防止权限或秘密泄露。

### G6：Self-Service 后

确认 analyst session 与 portal user session 没有混用，权限、主题、MFA 和附件访问分别验证。

### G7：切换前

用户批准默认入口、rollback、旧页面/API 删除清单、SOC 边界、运行手册和最终完成度。

## 8. “100% 完成”的统一定义

任何工期或模块只有同时满足以下条件才能标记为 Verified complete / 100% / Confidence=Yes：

- 计划中的用户流程全部可运行；
- legacy 页面/API parity 已核对，刻意不迁移项有用户批准；
- 正向与负向权限/租户测试通过；
- light/dark、桌面/窄屏、i18n/timezone、键盘和基本 a11y 通过；
- TypeScript typecheck、lint、unit/component、相关 API contract、E2E 和 build 通过；
- PHP 变更 php -l 及相关现有测试通过；
- Docker/子目录路由/刷新/深链接验证通过；
- 无未处理 console error、Promise rejection、secret/API key 泄露；
- progress、verification、decision log 和 handoff 已更新；
- 没有创建、运行或依赖 GitHub Actions。

若缺外部凭据、数据库、SSO 账户或真实集成，必须标 Blocked 或 Not verified，不能用 mock 结果冒充 100%。

## 9. 交给下一次 GPT Pro 的首个工期指令

> 执行 WP-01，然后执行 WP-02；WP-02 完成后必须停止，不得开始 BFF。  
> 首先检查分支、工作树以及是否已有本地/其他分支的 frontend/ 初次搭建，保护任何用户修改。  
> WP-01 建立 migration inventory、API matrix、route map、decision log、verification 与 handoff 结构。  
> WP-02 只完成 PHP 与 React/EUI 隔离式首次搭建：React 18、TypeScript strict、EUI、Vite、Router、providers、空 AppShell、测试骨架、运行时 BASE_URL 方案和 CODE_READING_GUIDE.md。  
> 不实现业务模块，不建立完整 BFF，不迁移 legacy API，不删除 PHP 页面。  
> 本地运行 typecheck、lint、unit、build、必要的 Docker/static smoke test，并更新 WORK_PROGRESS、VERIFICATION_REPORT 和 handoffs/WP-02.md。  
> 完成后给用户一份逐文件阅读顺序、需要用户选择的架构问题和可运行预览，然后等待 G1 审核。  
> 绝对禁止使用或创建 GitHub Actions。

## 10. 基准证据

- 仓库：https://github.com/TS00724/fork_freeitsm_react
- 公共 v1 路由：https://github.com/TS00724/fork_freeitsm_react/blob/main/api/v1/lib/routes.php
- 当前 CSRF 说明：https://github.com/TS00724/fork_freeitsm_react/blob/main/includes/request_guard.php
- 当前路由兼容层：https://github.com/TS00724/fork_freeitsm_react/blob/main/.htaccess
- EUI package manifest：https://github.com/elastic/eui/blob/main/packages/eui/package.json
- EUI LICENSE：https://github.com/elastic/eui/blob/main/packages/eui/LICENSE.txt

本计划中的数量是基准快照。每次开始新工期都以仓库当前 commit 为准复核，不以聊天记忆替代代码。
