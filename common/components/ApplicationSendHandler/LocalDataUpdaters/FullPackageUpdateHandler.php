<?php


namespace common\components\ApplicationSendHandler\LocalDataUpdaters;


use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\FullApplicationPackageBuilder;

class FullPackageUpdateHandler extends BaseUpdateHandler
{
    public function update(): bool
    {
        
        $response = \Yii::$app->soapClientWebApplication->load('GetEntrantPackage',
            [
                'Entrant' => $this->getApplication()->buildEntrantArray()
            ]);

        \Yii::$app->soapClientAbit->resetCurrentUserCache('NeedBlockAndUpdate', [$this->getApplication()->user_id]);
        \Yii::$app->soapClientAbit->resetCurrentUserCache('GetReference', [$this->getApplication()->user_id]); 

        return (new FullApplicationPackageBuilder($this->getApplication()))
            ->updateUserRefByFullPackage($response->return)
            ->receiveFiles()
            ->update($response->return);
    }

}