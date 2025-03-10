# oidivi-api

```
oidivi-api
├─ .editorconfig
├─ .git
│  ├─ config
│  ├─ description
│  ├─ HEAD
│  ├─ hooks
│  │  ├─ applypatch-msg.sample
│  │  ├─ commit-msg.sample
│  │  ├─ fsmonitor-watchman.sample
│  │  ├─ post-update.sample
│  │  ├─ pre-applypatch.sample
│  │  ├─ pre-commit.sample
│  │  ├─ pre-merge-commit.sample
│  │  ├─ pre-push.sample
│  │  ├─ pre-rebase.sample
│  │  ├─ pre-receive.sample
│  │  ├─ prepare-commit-msg.sample
│  │  ├─ push-to-checkout.sample
│  │  ├─ sendemail-validate.sample
│  │  └─ update.sample
│  ├─ index
│  ├─ info
│  │  └─ exclude
│  ├─ logs
│  │  ├─ HEAD
│  │  └─ refs
│  │     ├─ heads
│  │     │  └─ master
│  │     └─ remotes
│  │        └─ origin
│  │           └─ HEAD
│  ├─ objects
│  │  ├─ info
│  │  └─ pack
│  │     ├─ pack-9bbbca70b6558e1913531da528ba4b025e289796.idx
│  │     ├─ pack-9bbbca70b6558e1913531da528ba4b025e289796.pack
│  │     └─ pack-9bbbca70b6558e1913531da528ba4b025e289796.rev
│  ├─ packed-refs
│  └─ refs
│     ├─ heads
│     │  └─ master
│     ├─ remotes
│     │  └─ origin
│     │     └─ HEAD
│     └─ tags
├─ .gitattributes
├─ .gitignore
├─ app
│  ├─ Http
│  │  ├─ Controllers
│  │  │  ├─ Api
│  │  │  │  └─ V1
│  │  │  │     ├─ Admin
│  │  │  │     │  ├─ AdminAuthController.php
│  │  │  │     │  └─ AdminUserController.php
│  │  │  │     └─ Client
│  │  │  │        ├─ ClientAuthController.php
│  │  │  │        └─ ClientUserController.php
│  │  │  └─ Controller.php
│  │  ├─ Middleware
│  │  │  └─ CheckRole.php
│  │  └─ Resources
│  │     └─ ClientAuthResource.php
│  ├─ Models
│  │  ├─ Role.php
│  │  └─ User.php
│  └─ Providers
│     └─ AppServiceProvider.php
├─ artisan
├─ bootstrap
│  ├─ app.php
│  ├─ cache
│  │  ├─ .gitignore
│  │  ├─ packages.php
│  │  └─ services.php
│  └─ providers.php
├─ composer.json
├─ composer.lock
├─ config
│  ├─ app.php
│  ├─ auth.php
│  ├─ cache.php
│  ├─ database.php
│  ├─ filesystems.php
│  ├─ logging.php
│  ├─ mail.php
│  ├─ queue.php
│  ├─ sanctum.php
│  ├─ services.php
│  └─ session.php
├─ database
│  ├─ .gitignore
│  ├─ factories
│  │  ├─ RoleFactory.php
│  │  └─ UserFactory.php
│  ├─ migrations
│  │  ├─ 0001_01_01_000000_create_users_table.php
│  │  ├─ 0001_01_01_000001_create_cache_table.php
│  │  ├─ 0001_01_01_000002_create_jobs_table.php
│  │  ├─ 2025_01_02_213737_create_personal_access_tokens_table.php
│  │  ├─ 2025_01_02_225240_create_roles_table.php
│  │  └─ 2025_01_02_225414_create_role_user_table.php
│  └─ seeders
│     ├─ DatabaseSeeder.php
│     ├─ RoleSeeder.php
│     └─ UserSeeder.php
├─ lang
│  ├─ en
│  │  ├─ auth.php
│  │  ├─ messages.php
│  │  ├─ pagination.php
│  │  ├─ passwords.php
│  │  └─ validation.php
│  └─ es
│     ├─ auth.php
│     ├─ messages.php
│     ├─ pagination.php
│     ├─ passwords.php
│     └─ validation.php
├─ package.json
├─ phpunit.xml
├─ postcss.config.js
├─ public
│  ├─ .htaccess
│  ├─ favicon.ico
│  ├─ images
│  │  └─ default_avatar.png
│  ├─ index.php
│  └─ robots.txt
├─ README.md
├─ resources
│  ├─ css
│  │  └─ app.css
│  ├─ js
│  │  ├─ app.js
│  │  └─ bootstrap.js
│  └─ views
│     └─ welcome.blade.php
├─ routes
│  ├─ api.php
│  ├─ console.php
│  └─ web.php
├─ storage
│  ├─ app
│  │  ├─ .gitignore
│  │  ├─ private
│  │  │  └─ .gitignore
│  │  └─ public
│  │     └─ .gitignore
│  ├─ framework
│  │  ├─ .gitignore
│  │  ├─ cache
│  │  │  ├─ .gitignore
│  │  │  └─ data
│  │  │     └─ .gitignore
│  │  ├─ sessions
│  │  │  └─ .gitignore
│  │  ├─ testing
│  │  │  └─ .gitignore
│  │  └─ views
│  │     ├─ .gitignore
│  │     ├─ 0ec1e9e723705b69e0b06cc82fb6c726.php
│  │     ├─ 12eb1e46037ffa3039a35edfacccc803.php
│  │     ├─ 15b0550b34ba2a070b5adc0c9990fd32.php
│  │     ├─ 1dc8d7c9a0f90c3887716f62fd4fc987.php
│  │     ├─ 30890d101d76bc4da5750514f5a5a9b5.php
│  │     ├─ 4ef9a6573939c89bcfcbbd9a5504add7.php
│  │     ├─ 587c3af7f2c1687a74aa86b63cd5d6e5.php
│  │     ├─ 5b05fc162b2b4e1112a65cb1041acb30.php
│  │     ├─ 62dd6861fc2d4373f655eb3042f4e307.php
│  │     ├─ 6cebd6c180675162bb06c166de859a84.php
│  │     ├─ 6fd57f8915955d4fba4de8d5139b4719.php
│  │     ├─ 88b0a78bf8707dadc06c528d29566880.php
│  │     ├─ d07c7efdcfc7b9e52fc97c8d61f30bdc.php
│  │     ├─ d50b1b385dd148045ed7192fc8730b75.php
│  │     ├─ e3897b420453584174027f06d710acbf.php
│  │     └─ e51a82a4a9aa49ddaca54a9c1e6d1cdf.php
│  └─ logs
│     ├─ .gitignore
│     └─ laravel.log
├─ tailwind.config.js
├─ tests
│  ├─ Feature
│  │  └─ ExampleTest.php
│  ├─ TestCase.php
│  └─ Unit
│     └─ ExampleTest.php
└─ vite.config.js

```
