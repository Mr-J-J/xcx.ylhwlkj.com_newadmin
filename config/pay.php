<?php

return [
    'alipay' => [
        // 支付宝分配的 APPID
        'app_id' => env('ALI_APP_ID', '2021002171665007'),

        // 支付宝异步通知地址
        'notify_url' => '',

        // 支付成功后同步通知地址
        'return_url' => '',

        // 阿里公共密钥，验证签名时使用
        'ali_public_key' => app_path('Paycert').'/alipay/alipayCertPublicKey_RSA2.crt',

        // 自己的私钥，签名时使用
        'private_key' => env('ALI_PRIVATE_KEY', 'MIIEpAIBAAKCAQEAnFHz81jAECSGzcdktVQ4DbenCNyZAKWziNf27VHfAvXSKIVmQchaZbWxkOqQyBFHPajv2VV4KQYUPNAwJ11f1TuQsW7XiKSmHe5xwIVonIOAzpqQ+R4pJwHhEvOkcXr8U8xOlIDFK+dACmIDsSVvXYo/xhiI99ujMfJY9cferlbnnLp0rZKsuWnlfqTDYSfKoYaWxZa0Lv+1px5j54PkJViGbfAlUS9MxDC2ajoijXQHAnjNFS2ES/0FUNP/NjmIvAEfV8nmSvkIqsgfSVVBy1CS/EfniDGuC1hfr7Avkj43QP0a65nGRVrybYF9g4p9yz97PGg97H+FWp5J2UDXawIDAQABAoIBAHmc4HdPcoycfiuVlmnjzYAOgurIBFiWkShDLB3RdfhtmkRgE//ViU5bnnspod2kY5I63e5tKgBwdEojhXL6l9Xic1026IOr5HigyuqGX650OD+DzImix01LjvkZh7OphKkE55Vs4PiY/h+VxV2JzCwvpBHPgK+jtkDjVAZSqpFiXrtzVbE/Fc7XGpDcrP7vLo6BPSJePslsXND1Rf0pxcrYtuaXy6sJ8Kx/7upWkDhJdvPLgn4wXsVD7SdTkSDYSaxNeJ6j+6XjXwYdj6a2deS6jAuEtO+IfoTumV/SHvDYV2I8KPgnCpjoZttMhLfTpUmpoDFwJXqJ1kZrRky+hHECgYEA2AcWmoYnVIA1hZg/gHumxEhUomzV16Stmx2l+n9FWVW6yDL6egH9Q1b3gnTa1FKgygQh35SKe2oSs4/wWKzLL8Fo+pm41lDKJ3oF2VjtQzAikuY5kC6VIgMvouHLkhuAPvo2B4l/ZLI/8eHtqULzOc5bc1huI3+uAvSjJyBaNT8CgYEAuT6bHrUl1r4dlRKIqPR8WQkchtdJC3+MyamQZbMUCjC4MeWwlD+xT2rABPXv7/OUNWfO3hwSfFjPCxJgUcAaO8D3vQxpPVFDEclA2nqDTkDMuttBqdf49oelejQsTQhnjCbTjUVnLar9hSQT9u+ACGwe/313gLWLuT8Pgdb49tUCgYEAvevVanrA/yBMbaxBP+L15aC1j6rhgdmMq0+wiX706CpfsPxoi3VORLKjnXTAomQWaiSh5x9/dCr4UBcetccMR2rhsVgOUZTrdTNpCwGMbkJWUxHGz8S0Zheo/KlIQKae5D8z2wJ+FnhDkZMQT3vFvC945Pp8sgbhZEXRXmeqBx8CgYBaWSqXA4r8/aS3/F4i6XtsgYEkUwQRU5h7EvSWZY00myZ7T6eb5qS4MzyMLtdjlM2IbqT6t35cn4P7xm6r8KHa2vb5gYAio+uuVRYIeRBprjksOZwBFpEazHXs8F1bBOpb9OPhVyRHpcoYkcwa+Bzd/r3vpmYOpH0NULBjh1HOMQKBgQDPZJqfDhG0GxmKP3iIjydBAHxXythSrObTsbHu/9/CCQ2vhPwEFP9O/l+iB/eC8C0eZcUVAWHOAFdBFv6ke2+MzUQEzCSGEbOQoAdSU0GSq+K8cn+obzQDCEi8TDDarU9boL94QYxbJC0TQuj4Dm8t03WjzTi3sFl8vCG5zSGqvA=='),

        // 使用公钥证书模式，请配置下面两个参数，同时修改 ali_public_key 为以 .crt 结尾的支付宝公钥证书路径，如（./cert/alipayCertPublicKey_RSA2.crt）
        // 应用公钥证书路径
         'app_cert_public_key' => app_path('Paycert').'/alipay/appCertPublicKey_2021002171665007.crt',

        // 支付宝根证书路径
        'alipay_root_cert' => app_path('Paycert').'/alipay/alipayRootCert.crt',

        // optional，默认 warning；日志路径为：sys_get_temp_dir().'/logs/yansongda.pay.log'
        'log' => [
            'file' => storage_path('logs/alipay.log'),
        //  'level' => 'debug'
        //  'type' => 'single', // optional, 可选 daily.
        //  'max_file' => 30,
        ],

        // optional，设置此参数，将进入沙箱模式
        // 'mode' => 'dev',
    ],

    'wechat' => [
        // 公众号 APPID
        'app_id' => env('WECHAT_APP_ID', ''),

        // 小程序 APPID
        'miniapp_id' => env('WECHAT_MINIAPP_ID', ''),

        // APP 引用的 appid
        'appid' => env('WECHAT_APPID', ''),

        // 微信支付分配的微信商户号
        'mch_id' => env('WECHAT_MCH_ID', ''),

        // 微信支付异步通知地址
        'notify_url' => '',

        // 微信支付签名秘钥
        'key' => env('WECHAT_KEY', ''),

        // 客户端证书路径，退款、红包等需要用到。请填写绝对路径，linux 请确保权限问题。pem 格式。
        'cert_client' => '',

        // 客户端秘钥路径，退款、红包等需要用到。请填写绝对路径，linux 请确保权限问题。pem 格式。
        'cert_key' => '',

        // optional，默认 warning；日志路径为：sys_get_temp_dir().'/logs/yansongda.pay.log'
        'log' => [
            'file' => storage_path('logs/wechat.log'),
        //  'level' => 'debug'
        //  'type' => 'single', // optional, 可选 daily.
        //  'max_file' => 30,
        ],

        // optional
        // 'dev' 时为沙箱模式
        // 'hk' 时为东南亚节点
        // 'mode' => 'dev',
    ],
];
