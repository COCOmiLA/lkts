<?php

namespace common\components\SecureHeaders;

use hyperia\security\headers\ReferrerPolicy;
use hyperia\security\headers\StrictTransportSecurity;
use hyperia\security\headers\XContentTypeOptions;
use hyperia\security\headers\XFrameOptions;
use hyperia\security\headers\XPoweredBy;
use hyperia\security\headers\XssProtection;
use Yii;
use yii\base\Application;

class SecureHeaders extends \hyperia\security\Headers
{
    






    public function bootstrap($app)
    {
        $app->on(Application::EVENT_BEFORE_REQUEST, function () {
            if (is_a(Yii::$app, 'yii\web\Application')) {
                $headers = Yii::$app->response->headers;

                $headerPolicy = [
                    new XPoweredBy($this->xPoweredBy),
                    new XFrameOptions($this->xFrameOptions),
                    new XContentTypeOptions($this->contentTypeOptions),
                    new StrictTransportSecurity($this->strictTransportSecurity),
                    
                    
                    
                    new ReferrerPolicy($this->referrerPolicy),
                    new XssProtection($this->xssProtection, $this->reportUri),
                    new \common\components\SecureHeaders\ContentSecurityPolicy($this->cspDirectives, [
                        'requireSriForScript' => $this->requireSriForScript,
                        'requireSriForStyle' => $this->requireSriForStyle,
                        'blockAllMixedContent' => $this->blockAllMixedContent,
                        'upgradeInsecureRequests' => $this->upgradeInsecureRequests,
                    ], $this->reportUri)
                ];

                foreach ($headerPolicy as $policy) {
                    if ($policy->isValid()) {
                        $headers->set($policy->getName(), $policy->getValue());
                    }
                }
            }
        });
    }
}