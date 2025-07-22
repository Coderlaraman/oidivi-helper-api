<?php

app('router')->setCompiledRoutes(
    array (
  'compiled' => 
  array (
    0 => false,
    1 => 
    array (
      '/stripe/webhook' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'cashier.webhook',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sanctum/csrf-cookie' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sanctum.csrf-cookie',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/test' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Bhv708etJpARKqyB',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/terms' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::N2gJazvoE2ZV9TPO',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/stripe/webhook' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::mqHwTjYUov3AQnmF',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/admin/auth/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::lK52co0lsAniA4Ib',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/admin/auth/me' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::LZY3qmKZVFNJSWvv',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/admin/auth/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::KBbyPpDN4bbmf7qu',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/admin/categories' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.categories.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.categories.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/admin/service-requests' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'service-requests.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/admin/skills' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'skills.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'skills.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/admin/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/categories' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::IrXSBakdDi4W0Z4s',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/notifications' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::De7bFPa4NTe5JSrV',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/auth/email/verification-notification' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::V0tPp8kzZX88LUac',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/auth/forgot-password' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'user.auth.forgot-password',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/auth/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'user.auth.login',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/auth/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'user.auth.logout',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/auth/register' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'user.auth.register',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/auth/reset-password' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'user.auth.reset-password',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/payments/initiate' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::PYq0qQ5C8iVlRSOq',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/profile/photo' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::qo12kWkGKCx0uyc0',
          ),
          1 => NULL,
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::VV45n3SqHqNBbRih',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/profile/video' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::qLzYybysGnD6zB3o',
          ),
          1 => NULL,
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::RhPGbjKr0e2LomhJ',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/profile/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::m8nS5WvsNEQenzor',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/profile/me' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::k1SydppOTG1RjEmp',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/profile/search' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::B8OMCglaIQhi5x9z',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/profile/location/update' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::2kxDHeaYOOiOhePx',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/profile/skills' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::PkdU8qIM2QPCj4Fp',
          ),
          1 => NULL,
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/profile/update' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::a6zOWeP1wGf4nNEI',
          ),
          1 => NULL,
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/referrals' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::MzloRuBW0S3ifW7Z',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::nqi46uAbJrTljIoz',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/reports/reports' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::9FsakCGoiY4hxPmL',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::YQQNdXJvGmpmA1GT',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/reviews/reviews' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::srORzuxepTXIhUmV',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/my-service-requests' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::2vZe2dSKdcK1Y20g',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/my-service-requests/trash' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::gcTtRtrHwh1FHFqc',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/service-requests' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::XPRiJxIJ3tIY6DtG',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::UHWuf9K8VRQaN7c3',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/skills' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::mHZ7lI9tnrJjZwx3',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::v1zKThGCjZ4X4alc',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/subscriptions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ljd5Vlaxsd5tDVSF',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::OSZYuWtyr3ekIjn9',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/tickets/tickets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::lTTxu8FalBfONXfi',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::WPKiaC9irSqzMT9B',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/contracts' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::2AOrXVXUGSsA1rVI',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::mk4QSDjsBK4sfEjQ',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/transactions' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::LKuX21g3Izlxw0PX',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/my-offers' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::kLopKlPMwoKX7y9r',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/service-offers/received' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::I4Mvb7HmG2QFtOYK',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/user/service-offers/sent' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::mah27Xzi158VJuxL',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/v1/chats' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'chats.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/up' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::7nyvwajXKZAQbcxj',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::0GR1RzWblYgbRFLa',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/broadcasting/auth' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::dKUGqG7pCDD0qMNC',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/broadcasting/auth' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::bzoP5wTxEj51Mgdq',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'POST' => 1,
            'HEAD' => 2,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
    ),
    2 => 
    array (
      0 => '{^(?|/st(?|ripe/payment/([^/]++)(*:34)|orage/(.*)(*:51))|/api/v1/(?|admin/(?|categories/([^/]++)(?|(*:101)|/restore(*:117)|(*:125))|s(?|ervice\\-requests/([^/]++)(?|(*:166)|/restore(*:182))|kills/([^/]++)(?|(*:208)|/restore(*:224)))|users/([^/]++)(?|(*:251)|/(?|force(*:268)|toggle\\-active(*:290)|restore(*:305))|(*:314)))|user/(?|notifications/(?|([^/]++)(*:357)|unread\\-count(*:378)|([^/]++)/read(*:399)|read\\-all(*:416))|auth/email/verify/([^/]++)/([^/]++)(*:460)|re(?|ferrals/([^/]++)(?|(*:492))|views/reviews/([^/]++)(*:523))|my\\-service\\-requests/(?|offers/([^/]++)(*:572)|([^/]++)/(?|offers(*:598)|status(*:612))|offers/([^/]++)(*:636)|([^/]++)(?|/(?|offers(*:665)|restore(*:680))|(*:689)))|s(?|ervice\\-requests/([^/]++)(?|(*:731)|/(?|status(*:749)|offers(*:763)))|kills/(?|([^/]++)(*:790)|available(*:807))|ubscriptions/([^/]++)(?|(*:840)))|t(?|ickets/tickets/([^/]++)(?|(*:880)|/reply(*:894))|ransactions/([^/]++)(?|(*:926)|/(?|c(?|ancel(*:947)|omplete(*:962))|refund(*:977))))|contracts/([^/]++)(?|(*:1009))|public/profiles/([^/]++)(*:1043))|chats/(?|offers/([^/]++)(?|(*:1080)|/messages(*:1098))|([^/]++)/messages(*:1125))))/?$}sDu',
    ),
    3 => 
    array (
      34 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'cashier.payment',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      51 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'storage.local',
          ),
          1 => 
          array (
            0 => 'path',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      101 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.categories.destroy',
          ),
          1 => 
          array (
            0 => 'category',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.categories.show',
          ),
          1 => 
          array (
            0 => 'category',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      117 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.categories.restore',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      125 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.categories.update',
          ),
          1 => 
          array (
            0 => 'category',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      166 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'service-requests.show',
          ),
          1 => 
          array (
            0 => 'service_request',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'service-requests.update',
          ),
          1 => 
          array (
            0 => 'service_request',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'service-requests.destroy',
          ),
          1 => 
          array (
            0 => 'service_request',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      182 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.serviceRequests.restore',
          ),
          1 => 
          array (
            0 => 'serviceRequest',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      208 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'skills.show',
          ),
          1 => 
          array (
            0 => 'skill',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'skills.update',
          ),
          1 => 
          array (
            0 => 'skill',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'skills.destroy',
          ),
          1 => 
          array (
            0 => 'skill',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      224 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.skills.restore',
          ),
          1 => 
          array (
            0 => 'skill',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      251 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.destroy',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      268 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.forceDelete',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      290 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.toggleActive',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      305 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.restore',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      314 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.show',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      357 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::jSvgyY6UVaJq8tDs',
          ),
          1 => 
          array (
            0 => 'notification',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      378 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::xMOKNBltj7N6Al66',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      399 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::P8CsnkdAiIhZxBG1',
          ),
          1 => 
          array (
            0 => 'notification',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      416 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ROlUXSe6iPDlch6K',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      460 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'verification.verify',
          ),
          1 => 
          array (
            0 => 'id',
            1 => 'hash',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      492 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::5RI5U6V9wIcTenyA',
          ),
          1 => 
          array (
            0 => 'referral',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::A1hfzlBxEbeZ6Ers',
          ),
          1 => 
          array (
            0 => 'referral',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'generated::iz22R5o0loGEOpHq',
          ),
          1 => 
          array (
            0 => 'referral',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      523 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Pc7uWVuwk9B70wua',
          ),
          1 => 
          array (
            0 => 'userId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      572 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::iKl4FeaMsIWmNY1c',
          ),
          1 => 
          array (
            0 => 'offer',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      598 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::k4lUuOy7afRFCBTS',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      612 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::DoQ21aOJhLjd8AmA',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      636 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::MsT32kBXSe15SX7e',
          ),
          1 => 
          array (
            0 => 'offer',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      665 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::jD19acvvyRdSw7pC',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      680 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Rwx21WMD5VWsCToY',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      689 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::OjfB7gzhAj56AgBY',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      731 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::XcXxCr56fVNbNcyw',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::f2xJNjDlUPbbL0ML',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      749 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::8HGLLesNiWPWcksY',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'PATCH' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      763 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::lbt3xk6nCSGP0v3x',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      790 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::iOndtpAXW06h5WzN',
          ),
          1 => 
          array (
            0 => 'skill',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      807 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::tW1hQR3cS2V0aWFZ',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      840 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::NMxhCJfRtN7T1yol',
          ),
          1 => 
          array (
            0 => 'subscription',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::AKS9nRmxm9GWQxV7',
          ),
          1 => 
          array (
            0 => 'subscription',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'generated::usXHXZBfyMiUUi6Q',
          ),
          1 => 
          array (
            0 => 'subscription',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      880 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::lhIIlxmn4546HOw2',
          ),
          1 => 
          array (
            0 => 'ticket',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      894 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::kzsoTnHZEa6nbX6D',
          ),
          1 => 
          array (
            0 => 'ticket',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      926 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ZpnYQKOWUkYFfV0E',
          ),
          1 => 
          array (
            0 => 'transaction',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      947 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::71ZFi4VgikPyYCQO',
          ),
          1 => 
          array (
            0 => 'transaction',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      962 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::LIjPldJi74tJdNFe',
          ),
          1 => 
          array (
            0 => 'transaction',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      977 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::QQjfE2iPmC5nJCap',
          ),
          1 => 
          array (
            0 => 'transaction',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1009 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::EQmzYObN4SrfE1yF',
          ),
          1 => 
          array (
            0 => 'contract',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::i6FxLRmaAikyywKY',
          ),
          1 => 
          array (
            0 => 'contract',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'generated::DI6kCzfZ1b7ghD9n',
          ),
          1 => 
          array (
            0 => 'contract',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1043 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::mgAUbtFrreNBDic7',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1080 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::JxxnylrL5MxjibHw',
          ),
          1 => 
          array (
            0 => 'offerId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1098 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::EcKnieq0Qg2mMgHe',
          ),
          1 => 
          array (
            0 => 'offerId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1125 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::iqgMnvIijpsYCPmR',
          ),
          1 => 
          array (
            0 => 'chat',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => NULL,
          1 => NULL,
          2 => NULL,
          3 => NULL,
          4 => false,
          5 => false,
          6 => 0,
        ),
      ),
    ),
    4 => NULL,
  ),
  'attributes' => 
  array (
    'cashier.payment' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'stripe/payment/{id}',
      'action' => 
      array (
        'uses' => 'Laravel\\Cashier\\Http\\Controllers\\PaymentController@show',
        'controller' => 'Laravel\\Cashier\\Http\\Controllers\\PaymentController@show',
        'as' => 'cashier.payment',
        'namespace' => 'Laravel\\Cashier\\Http\\Controllers',
        'prefix' => 'stripe',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'cashier.webhook' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'stripe/webhook',
      'action' => 
      array (
        'uses' => 'Laravel\\Cashier\\Http\\Controllers\\WebhookController@handleWebhook',
        'controller' => 'Laravel\\Cashier\\Http\\Controllers\\WebhookController@handleWebhook',
        'as' => 'cashier.webhook',
        'namespace' => 'Laravel\\Cashier\\Http\\Controllers',
        'prefix' => 'stripe',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sanctum.csrf-cookie' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sanctum/csrf-cookie',
      'action' => 
      array (
        'uses' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'controller' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'namespace' => NULL,
        'prefix' => 'sanctum',
        'where' => 
        array (
        ),
        'middleware' => 
        array (
          0 => 'web',
        ),
        'as' => 'sanctum.csrf-cookie',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Bhv708etJpARKqyB' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/test',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:58:"fn() => \\response()->json([\'message\' => \'API is working\'])";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000007cb0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::Bhv708etJpARKqyB',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::N2gJazvoE2ZV9TPO' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/terms',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\TermsController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\TermsController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::N2gJazvoE2ZV9TPO',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::mqHwTjYUov3AQnmF' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/stripe/webhook',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Payments\\UserPaymentController@handleStripeWebhook',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Payments\\UserPaymentController@handleStripeWebhook',
        'namespace' => NULL,
        'prefix' => 'api/v1',
        'where' => 
        array (
        ),
        'as' => 'generated::mqHwTjYUov3AQnmF',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::lK52co0lsAniA4Ib' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/admin/auth/login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Auth\\AdminAuthController@login',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Auth\\AdminAuthController@login',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/auth',
        'where' => 
        array (
        ),
        'as' => 'generated::lK52co0lsAniA4Ib',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::LZY3qmKZVFNJSWvv' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/admin/auth/me',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Auth\\AdminAuthController@me',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Auth\\AdminAuthController@me',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::LZY3qmKZVFNJSWvv',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::KBbyPpDN4bbmf7qu' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/admin/auth/logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Auth\\AdminAuthController@logout',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Auth\\AdminAuthController@logout',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin',
        'where' => 
        array (
        ),
        'as' => 'generated::KBbyPpDN4bbmf7qu',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.categories.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/admin/categories/{category}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Categories\\AdminCategoryController@destroy',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Categories\\AdminCategoryController@destroy',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/categories',
        'where' => 
        array (
        ),
        'as' => 'admin.categories.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.categories.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/admin/categories',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Categories\\AdminCategoryController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Categories\\AdminCategoryController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/categories',
        'where' => 
        array (
        ),
        'as' => 'admin.categories.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.categories.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/admin/categories/{category}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Categories\\AdminCategoryController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Categories\\AdminCategoryController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/categories',
        'where' => 
        array (
        ),
        'as' => 'admin.categories.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.categories.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/admin/categories',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Categories\\AdminCategoryController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Categories\\AdminCategoryController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/categories',
        'where' => 
        array (
        ),
        'as' => 'admin.categories.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.categories.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/admin/categories/{id}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Categories\\AdminCategoryController@restore',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Categories\\AdminCategoryController@restore',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/categories',
        'where' => 
        array (
        ),
        'as' => 'admin.categories.restore',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.categories.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/v1/admin/categories/{category}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Categories\\AdminCategoryController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Categories\\AdminCategoryController@update',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/categories',
        'where' => 
        array (
        ),
        'as' => 'admin.categories.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'service-requests.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/admin/service-requests',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'as' => 'service-requests.index',
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\ServiceRequests\\AdminServiceRequestController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\ServiceRequests\\AdminServiceRequestController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'service-requests.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/admin/service-requests/{service_request}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'as' => 'service-requests.show',
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\ServiceRequests\\AdminServiceRequestController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\ServiceRequests\\AdminServiceRequestController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'service-requests.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'api/v1/admin/service-requests/{service_request}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'as' => 'service-requests.update',
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\ServiceRequests\\AdminServiceRequestController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\ServiceRequests\\AdminServiceRequestController@update',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'service-requests.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/admin/service-requests/{service_request}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'as' => 'service-requests.destroy',
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\ServiceRequests\\AdminServiceRequestController@destroy',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\ServiceRequests\\AdminServiceRequestController@destroy',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.serviceRequests.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/admin/service-requests/{serviceRequest}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\ServiceRequests\\AdminServiceRequestController@restore',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\ServiceRequests\\AdminServiceRequestController@restore',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.serviceRequests.restore',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'skills.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/admin/skills',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'as' => 'skills.index',
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Skills\\AdminSkillController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Skills\\AdminSkillController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'skills.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/admin/skills',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'as' => 'skills.store',
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Skills\\AdminSkillController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Skills\\AdminSkillController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'skills.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/admin/skills/{skill}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'as' => 'skills.show',
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Skills\\AdminSkillController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Skills\\AdminSkillController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'skills.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'api/v1/admin/skills/{skill}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'as' => 'skills.update',
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Skills\\AdminSkillController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Skills\\AdminSkillController@update',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'skills.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/admin/skills/{skill}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'as' => 'skills.destroy',
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Skills\\AdminSkillController@destroy',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Skills\\AdminSkillController@destroy',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.skills.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/admin/skills/{skill}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Skills\\AdminSkillController@restore',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Skills\\AdminSkillController@restore',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin',
        'where' => 
        array (
        ),
        'as' => 'admin.skills.restore',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@destroy',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@destroy',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/users',
        'where' => 
        array (
        ),
        'as' => 'admin.users.destroy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.forceDelete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/admin/users/{user}/force',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@forceDelete',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@forceDelete',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/users',
        'where' => 
        array (
        ),
        'as' => 'admin.users.forceDelete',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/users',
        'where' => 
        array (
        ),
        'as' => 'admin.users.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/users',
        'where' => 
        array (
        ),
        'as' => 'admin.users.show',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.toggleActive' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'api/v1/admin/users/{user}/toggle-active',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@toggleActive',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@toggleActive',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/users',
        'where' => 
        array (
        ),
        'as' => 'admin.users.toggleActive',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/users',
        'where' => 
        array (
        ),
        'as' => 'admin.users.store',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.restore' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/admin/users/{user}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@restore',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@restore',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/users',
        'where' => 
        array (
        ),
        'as' => 'admin.users.restore',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/v1/admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'role:admin',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Admin\\Users\\AdminUserController@update',
        'namespace' => NULL,
        'prefix' => 'api/v1/admin/users',
        'where' => 
        array (
        ),
        'as' => 'admin.users.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::IrXSBakdDi4W0Z4s' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/categories',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Categories\\UserCategoryController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Categories\\UserCategoryController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/categories',
        'where' => 
        array (
        ),
        'as' => 'generated::IrXSBakdDi4W0Z4s',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::jSvgyY6UVaJq8tDs' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/user/notifications/{notification}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Notifications\\UserNotificationController@destroy',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Notifications\\UserNotificationController@destroy',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/notifications',
        'where' => 
        array (
        ),
        'as' => 'generated::jSvgyY6UVaJq8tDs',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::De7bFPa4NTe5JSrV' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/notifications',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Notifications\\UserNotificationController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Notifications\\UserNotificationController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/notifications',
        'where' => 
        array (
        ),
        'as' => 'generated::De7bFPa4NTe5JSrV',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::xMOKNBltj7N6Al66' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/notifications/unread-count',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Notifications\\UserNotificationController@getUnreadCount',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Notifications\\UserNotificationController@getUnreadCount',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/notifications',
        'where' => 
        array (
        ),
        'as' => 'generated::xMOKNBltj7N6Al66',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::P8CsnkdAiIhZxBG1' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'api/v1/user/notifications/{notification}/read',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Notifications\\UserNotificationController@markAsRead',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Notifications\\UserNotificationController@markAsRead',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/notifications',
        'where' => 
        array (
        ),
        'as' => 'generated::P8CsnkdAiIhZxBG1',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ROlUXSe6iPDlch6K' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'api/v1/user/notifications/read-all',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Notifications\\UserNotificationController@markAllAsRead',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Notifications\\UserNotificationController@markAllAsRead',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/notifications',
        'where' => 
        array (
        ),
        'as' => 'generated::ROlUXSe6iPDlch6K',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::V0tPp8kzZX88LUac' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/auth/email/verification-notification',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Auth\\UserEmailVerificationController@sendVerificationEmail',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Auth\\UserEmailVerificationController@sendVerificationEmail',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/auth',
        'where' => 
        array (
        ),
        'as' => 'generated::V0tPp8kzZX88LUac',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'verification.verify' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/auth/email/verify/{id}/{hash}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Auth\\UserEmailVerificationController@verify',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Auth\\UserEmailVerificationController@verify',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/auth',
        'where' => 
        array (
        ),
        'as' => 'verification.verify',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'user.auth.forgot-password' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/auth/forgot-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Auth\\UserAuthController@forgotPassword',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Auth\\UserAuthController@forgotPassword',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/auth',
        'where' => 
        array (
        ),
        'as' => 'user.auth.forgot-password',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'user.auth.login' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/auth/login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Auth\\UserAuthController@login',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Auth\\UserAuthController@login',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/auth',
        'where' => 
        array (
        ),
        'as' => 'user.auth.login',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'user.auth.logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/auth/logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Auth\\UserAuthController@logout',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Auth\\UserAuthController@logout',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/auth',
        'where' => 
        array (
        ),
        'as' => 'user.auth.logout',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'user.auth.register' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/auth/register',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Auth\\UserAuthController@register',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Auth\\UserAuthController@register',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/auth',
        'where' => 
        array (
        ),
        'as' => 'user.auth.register',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'user.auth.reset-password' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/auth/reset-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Auth\\UserAuthController@resetPassword',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Auth\\UserAuthController@resetPassword',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/auth',
        'where' => 
        array (
        ),
        'as' => 'user.auth.reset-password',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::PYq0qQ5C8iVlRSOq' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/payments/initiate',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Payments\\UserPaymentController@initiatePayment',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Payments\\UserPaymentController@initiatePayment',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/payments',
        'where' => 
        array (
        ),
        'as' => 'generated::PYq0qQ5C8iVlRSOq',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::qo12kWkGKCx0uyc0' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/user/profile/photo',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@deleteProfilePhoto',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@deleteProfilePhoto',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/profile',
        'where' => 
        array (
        ),
        'as' => 'generated::qo12kWkGKCx0uyc0',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::qLzYybysGnD6zB3o' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/user/profile/video',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@deleteProfileVideo',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@deleteProfileVideo',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/profile',
        'where' => 
        array (
        ),
        'as' => 'generated::qLzYybysGnD6zB3o',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::m8nS5WvsNEQenzor' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/profile/dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@dashboard',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@dashboard',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/profile',
        'where' => 
        array (
        ),
        'as' => 'generated::m8nS5WvsNEQenzor',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::k1SydppOTG1RjEmp' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/profile/me',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@showProfile',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@showProfile',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/profile',
        'where' => 
        array (
        ),
        'as' => 'generated::k1SydppOTG1RjEmp',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::B8OMCglaIQhi5x9z' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/profile/search',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@search',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@search',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/profile',
        'where' => 
        array (
        ),
        'as' => 'generated::B8OMCglaIQhi5x9z',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::2kxDHeaYOOiOhePx' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/profile/location/update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Locations\\UserLocationController@updateLocation',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Locations\\UserLocationController@updateLocation',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/profile',
        'where' => 
        array (
        ),
        'as' => 'generated::2kxDHeaYOOiOhePx',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::VV45n3SqHqNBbRih' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/profile/photo',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@uploadProfilePhoto',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@uploadProfilePhoto',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/profile',
        'where' => 
        array (
        ),
        'as' => 'generated::VV45n3SqHqNBbRih',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::RhPGbjKr0e2LomhJ' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/profile/video',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@uploadProfileVideo',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@uploadProfileVideo',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/profile',
        'where' => 
        array (
        ),
        'as' => 'generated::RhPGbjKr0e2LomhJ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::PkdU8qIM2QPCj4Fp' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/v1/user/profile/skills',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@updateSkills',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@updateSkills',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/profile',
        'where' => 
        array (
        ),
        'as' => 'generated::PkdU8qIM2QPCj4Fp',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::a6zOWeP1wGf4nNEI' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/v1/user/profile/update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@updateProfile',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@updateProfile',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/profile',
        'where' => 
        array (
        ),
        'as' => 'generated::a6zOWeP1wGf4nNEI',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::5RI5U6V9wIcTenyA' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/user/referrals/{referral}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Referrals\\UserReferralController@destroy',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Referrals\\UserReferralController@destroy',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/referrals',
        'where' => 
        array (
        ),
        'as' => 'generated::5RI5U6V9wIcTenyA',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::MzloRuBW0S3ifW7Z' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/referrals',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Referrals\\UserReferralController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Referrals\\UserReferralController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/referrals',
        'where' => 
        array (
        ),
        'as' => 'generated::MzloRuBW0S3ifW7Z',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::A1hfzlBxEbeZ6Ers' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/referrals/{referral}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Referrals\\UserReferralController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Referrals\\UserReferralController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/referrals',
        'where' => 
        array (
        ),
        'as' => 'generated::A1hfzlBxEbeZ6Ers',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::nqi46uAbJrTljIoz' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/referrals',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Referrals\\UserReferralController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Referrals\\UserReferralController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/referrals',
        'where' => 
        array (
        ),
        'as' => 'generated::nqi46uAbJrTljIoz',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::iz22R5o0loGEOpHq' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/v1/user/referrals/{referral}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Referrals\\UserReferralController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Referrals\\UserReferralController@update',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/referrals',
        'where' => 
        array (
        ),
        'as' => 'generated::iz22R5o0loGEOpHq',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::9FsakCGoiY4hxPmL' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/reports/reports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Reports\\UserReportController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Reports\\UserReportController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/reports',
        'where' => 
        array (
        ),
        'as' => 'generated::9FsakCGoiY4hxPmL',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::YQQNdXJvGmpmA1GT' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/reports/reports',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Reports\\UserReportController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Reports\\UserReportController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/reports',
        'where' => 
        array (
        ),
        'as' => 'generated::YQQNdXJvGmpmA1GT',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Pc7uWVuwk9B70wua' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/reviews/reviews/{userId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Reviews\\UserReviewController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Reviews\\UserReviewController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/reviews',
        'where' => 
        array (
        ),
        'as' => 'generated::Pc7uWVuwk9B70wua',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::srORzuxepTXIhUmV' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/reviews/reviews',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Reviews\\UserReviewController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Reviews\\UserReviewController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/reviews',
        'where' => 
        array (
        ),
        'as' => 'generated::srORzuxepTXIhUmV',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::2vZe2dSKdcK1Y20g' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/my-service-requests',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@myServiceRequests',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@myServiceRequests',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/my-service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::2vZe2dSKdcK1Y20g',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::iKl4FeaMsIWmNY1c' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/my-service-requests/offers/{offer}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@showOffer',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@showOffer',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/my-service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::iKl4FeaMsIWmNY1c',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::gcTtRtrHwh1FHFqc' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/my-service-requests/trash',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@trashedRequests',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@trashedRequests',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/my-service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::gcTtRtrHwh1FHFqc',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::k4lUuOy7afRFCBTS' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/my-service-requests/{id}/offers',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@requestOffers',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@requestOffers',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/my-service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::k4lUuOy7afRFCBTS',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::DoQ21aOJhLjd8AmA' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'api/v1/user/my-service-requests/{id}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@updateStatus',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/my-service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::DoQ21aOJhLjd8AmA',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::MsT32kBXSe15SX7e' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'api/v1/user/my-service-requests/offers/{offer}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@update',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/my-service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::MsT32kBXSe15SX7e',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::jD19acvvyRdSw7pC' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/my-service-requests/{id}/offers',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/my-service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::jD19acvvyRdSw7pC',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Rwx21WMD5VWsCToY' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/my-service-requests/{id}/restore',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@restore',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@restore',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/my-service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::Rwx21WMD5VWsCToY',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::OjfB7gzhAj56AgBY' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/v1/user/my-service-requests/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@update',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/my-service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::OjfB7gzhAj56AgBY',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::XcXxCr56fVNbNcyw' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/user/service-requests/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@destroy',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@destroy',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::XcXxCr56fVNbNcyw',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::XPRiJxIJ3tIY6DtG' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/service-requests',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::XPRiJxIJ3tIY6DtG',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::f2xJNjDlUPbbL0ML' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/service-requests/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::f2xJNjDlUPbbL0ML',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::8HGLLesNiWPWcksY' => 
    array (
      'methods' => 
      array (
        0 => 'PATCH',
      ),
      'uri' => 'api/v1/user/service-requests/{id}/status',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@updateStatus',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@updateStatus',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::8HGLLesNiWPWcksY',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::UHWuf9K8VRQaN7c3' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/service-requests',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceRequests\\UserServiceRequestController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::UHWuf9K8VRQaN7c3',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::lbt3xk6nCSGP0v3x' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/service-requests/{id}/offers',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/service-requests',
        'where' => 
        array (
        ),
        'as' => 'generated::lbt3xk6nCSGP0v3x',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::iOndtpAXW06h5WzN' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/user/skills/{skill}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Skills\\UserSkillController@destroy',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Skills\\UserSkillController@destroy',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/skills',
        'where' => 
        array (
        ),
        'as' => 'generated::iOndtpAXW06h5WzN',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::mHZ7lI9tnrJjZwx3' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/skills',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Skills\\UserSkillController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Skills\\UserSkillController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/skills',
        'where' => 
        array (
        ),
        'as' => 'generated::mHZ7lI9tnrJjZwx3',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::tW1hQR3cS2V0aWFZ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/skills/available',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Skills\\UserSkillController@available',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Skills\\UserSkillController@available',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/skills',
        'where' => 
        array (
        ),
        'as' => 'generated::tW1hQR3cS2V0aWFZ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::v1zKThGCjZ4X4alc' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/skills',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Skills\\UserSkillController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Skills\\UserSkillController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/skills',
        'where' => 
        array (
        ),
        'as' => 'generated::v1zKThGCjZ4X4alc',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::NMxhCJfRtN7T1yol' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/user/subscriptions/{subscription}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Subscriptions\\UserSubscriptionController@destroy',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Subscriptions\\UserSubscriptionController@destroy',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/subscriptions',
        'where' => 
        array (
        ),
        'as' => 'generated::NMxhCJfRtN7T1yol',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ljd5Vlaxsd5tDVSF' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/subscriptions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Subscriptions\\UserSubscriptionController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Subscriptions\\UserSubscriptionController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/subscriptions',
        'where' => 
        array (
        ),
        'as' => 'generated::ljd5Vlaxsd5tDVSF',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::AKS9nRmxm9GWQxV7' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/subscriptions/{subscription}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Subscriptions\\UserSubscriptionController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Subscriptions\\UserSubscriptionController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/subscriptions',
        'where' => 
        array (
        ),
        'as' => 'generated::AKS9nRmxm9GWQxV7',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::OSZYuWtyr3ekIjn9' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/subscriptions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Subscriptions\\UserSubscriptionController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Subscriptions\\UserSubscriptionController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/subscriptions',
        'where' => 
        array (
        ),
        'as' => 'generated::OSZYuWtyr3ekIjn9',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::usXHXZBfyMiUUi6Q' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/v1/user/subscriptions/{subscription}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Subscriptions\\UserSubscriptionController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Subscriptions\\UserSubscriptionController@update',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/subscriptions',
        'where' => 
        array (
        ),
        'as' => 'generated::usXHXZBfyMiUUi6Q',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::lTTxu8FalBfONXfi' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/tickets/tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Tickets\\UserTicketController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Tickets\\UserTicketController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/tickets',
        'where' => 
        array (
        ),
        'as' => 'generated::lTTxu8FalBfONXfi',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::lhIIlxmn4546HOw2' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/tickets/tickets/{ticket}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Tickets\\UserTicketController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Tickets\\UserTicketController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/tickets',
        'where' => 
        array (
        ),
        'as' => 'generated::lhIIlxmn4546HOw2',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::WPKiaC9irSqzMT9B' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/tickets/tickets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Tickets\\UserTicketController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Tickets\\UserTicketController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/tickets',
        'where' => 
        array (
        ),
        'as' => 'generated::WPKiaC9irSqzMT9B',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::kzsoTnHZEa6nbX6D' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/tickets/tickets/{ticket}/reply',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Tickets\\UserTicketController@reply',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Tickets\\UserTicketController@reply',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/tickets',
        'where' => 
        array (
        ),
        'as' => 'generated::kzsoTnHZEa6nbX6D',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::EQmzYObN4SrfE1yF' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'api/v1/user/contracts/{contract}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Contracts\\UserContractController@destroy',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Contracts\\UserContractController@destroy',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/contracts',
        'where' => 
        array (
        ),
        'as' => 'generated::EQmzYObN4SrfE1yF',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::2AOrXVXUGSsA1rVI' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/contracts',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Contracts\\UserContractController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Contracts\\UserContractController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/contracts',
        'where' => 
        array (
        ),
        'as' => 'generated::2AOrXVXUGSsA1rVI',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::i6FxLRmaAikyywKY' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/contracts/{contract}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Contracts\\UserContractController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Contracts\\UserContractController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/contracts',
        'where' => 
        array (
        ),
        'as' => 'generated::i6FxLRmaAikyywKY',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::mk4QSDjsBK4sfEjQ' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/contracts',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Contracts\\UserContractController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Contracts\\UserContractController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/contracts',
        'where' => 
        array (
        ),
        'as' => 'generated::mk4QSDjsBK4sfEjQ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::DI6kCzfZ1b7ghD9n' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'api/v1/user/contracts/{contract}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
          3 => 'verified',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Contracts\\UserContractController@update',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Contracts\\UserContractController@update',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/contracts',
        'where' => 
        array (
        ),
        'as' => 'generated::DI6kCzfZ1b7ghD9n',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::LKuX21g3Izlxw0PX' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/transactions',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Transactions\\UserTransactionController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Transactions\\UserTransactionController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/transactions',
        'where' => 
        array (
        ),
        'as' => 'generated::LKuX21g3Izlxw0PX',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ZpnYQKOWUkYFfV0E' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/transactions/{transaction}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Transactions\\UserTransactionController@show',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Transactions\\UserTransactionController@show',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/transactions',
        'where' => 
        array (
        ),
        'as' => 'generated::ZpnYQKOWUkYFfV0E',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::71ZFi4VgikPyYCQO' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/transactions/{transaction}/cancel',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Transactions\\UserTransactionController@cancel',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Transactions\\UserTransactionController@cancel',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/transactions',
        'where' => 
        array (
        ),
        'as' => 'generated::71ZFi4VgikPyYCQO',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::LIjPldJi74tJdNFe' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/transactions/{transaction}/complete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Transactions\\UserTransactionController@complete',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Transactions\\UserTransactionController@complete',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/transactions',
        'where' => 
        array (
        ),
        'as' => 'generated::LIjPldJi74tJdNFe',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::QQjfE2iPmC5nJCap' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/user/transactions/{transaction}/refund',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Transactions\\UserTransactionController@refund',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Transactions\\UserTransactionController@refund',
        'namespace' => NULL,
        'prefix' => 'api/v1/user/transactions',
        'where' => 
        array (
        ),
        'as' => 'generated::QQjfE2iPmC5nJCap',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::kLopKlPMwoKX7y9r' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/my-offers',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@myOffers',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@myOffers',
        'namespace' => NULL,
        'prefix' => 'api/v1/user',
        'where' => 
        array (
        ),
        'as' => 'generated::kLopKlPMwoKX7y9r',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::I4Mvb7HmG2QFtOYK' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/service-offers/received',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@receivedOffers',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@receivedOffers',
        'namespace' => NULL,
        'prefix' => 'api/v1/user',
        'where' => 
        array (
        ),
        'as' => 'generated::I4Mvb7HmG2QFtOYK',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::mah27Xzi158VJuxL' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/service-offers/sent',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@sentOffers',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\ServiceOffers\\UserServiceOfferController@sentOffers',
        'namespace' => NULL,
        'prefix' => 'api/v1/user',
        'where' => 
        array (
        ),
        'as' => 'generated::mah27Xzi158VJuxL',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::mgAUbtFrreNBDic7' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/user/public/profiles/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@showPublicProfile',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\User\\Profiles\\UserProfileController@showPublicProfile',
        'namespace' => NULL,
        'prefix' => 'api/v1/user',
        'where' => 
        array (
        ),
        'as' => 'generated::mgAUbtFrreNBDic7',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'chats.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/chats',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Chat\\ChatController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Chat\\ChatController@index',
        'namespace' => NULL,
        'prefix' => 'api/v1/chats',
        'where' => 
        array (
        ),
        'as' => 'chats.index',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::JxxnylrL5MxjibHw' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/v1/chats/offers/{offerId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Chat\\ChatController@showOrCreate',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Chat\\ChatController@showOrCreate',
        'namespace' => NULL,
        'prefix' => 'api/v1/chats',
        'where' => 
        array (
        ),
        'as' => 'generated::JxxnylrL5MxjibHw',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::EcKnieq0Qg2mMgHe' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/chats/offers/{offerId}/messages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Chat\\MessageController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Chat\\MessageController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/chats',
        'where' => 
        array (
        ),
        'as' => 'generated::EcKnieq0Qg2mMgHe',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::iqgMnvIijpsYCPmR' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/v1/chats/{chat}/messages',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'locale',
          2 => 'auth:sanctum',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\V1\\Chat\\MessageController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\V1\\Chat\\MessageController@store',
        'namespace' => NULL,
        'prefix' => 'api/v1/chats',
        'where' => 
        array (
        ),
        'as' => 'generated::iqgMnvIijpsYCPmR',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::7nyvwajXKZAQbcxj' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'up',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:849:"function () {
                    $exception = null;

                    try {
                        \\Illuminate\\Support\\Facades\\Event::dispatch(new \\Illuminate\\Foundation\\Events\\DiagnosingHealth);
                    } catch (\\Throwable $e) {
                        if (app()->hasDebugModeEnabled()) {
                            throw $e;
                        }

                        report($e);

                        $exception = $e->getMessage();
                    }

                    return response(\\Illuminate\\Support\\Facades\\View::file(\'C:\\\\Users\\\\Coderman\\\\projects\\\\oidivi-helper-api\\\\vendor\\\\laravel\\\\framework\\\\src\\\\Illuminate\\\\Foundation\\\\Configuration\'.\'/../resources/health-up.blade.php\', [
                        \'exception\' => $exception,
                    ]), status: $exception ? 500 : 200);
                }";s:5:"scope";s:54:"Illuminate\\Foundation\\Configuration\\ApplicationBuilder";s:4:"this";N;s:4:"self";s:32:"00000000000007c90000000000000000";}}',
        'as' => 'generated::7nyvwajXKZAQbcxj',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::0GR1RzWblYgbRFLa' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '/',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:44:"function () {
    return \\view(\'welcome\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000007d00000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::0GR1RzWblYgbRFLa',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::dKUGqG7pCDD0qMNC' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'broadcasting/auth',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'auth:sanctum',
        ),
        'uses' => '\\Illuminate\\Broadcasting\\BroadcastController@authenticate',
        'controller' => '\\Illuminate\\Broadcasting\\BroadcastController@authenticate',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
          0 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
        ),
        'as' => 'generated::dKUGqG7pCDD0qMNC',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::bzoP5wTxEj51Mgdq' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
        2 => 'HEAD',
      ),
      'uri' => 'api/broadcasting/auth',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:sanctum',
        ),
        'uses' => '\\Illuminate\\Broadcasting\\BroadcastController@authenticate',
        'controller' => '\\Illuminate\\Broadcasting\\BroadcastController@authenticate',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'excluded_middleware' => 
        array (
          0 => 'Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken',
        ),
        'as' => 'generated::bzoP5wTxEj51Mgdq',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'storage.local' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'storage/{path}',
      'action' => 
      array (
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:3:{s:4:"disk";s:5:"local";s:6:"config";a:4:{s:6:"driver";s:5:"local";s:4:"root";s:64:"C:\\Users\\Coderman\\projects\\oidivi-helper-api\\storage\\app/private";s:5:"serve";b:1;s:5:"throw";b:0;}s:12:"isProduction";b:0;}s:8:"function";s:323:"function (\\Illuminate\\Http\\Request $request, string $path) use ($disk, $config, $isProduction) {
                    return (new \\Illuminate\\Filesystem\\ServeFile(
                        $disk,
                        $config,
                        $isProduction
                    ))($request, $path);
                }";s:5:"scope";s:47:"Illuminate\\Filesystem\\FilesystemServiceProvider";s:4:"this";N;s:4:"self";s:32:"00000000000008570000000000000000";}}',
        'as' => 'storage.local',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'path' => '.*',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
  ),
)
);
