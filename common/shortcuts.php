<?php












function getMyId()
{
    return Yii::$app->user->getId();
}






function render($view, $params = [])
{
    return Yii::$app->controller->render($view, $params);
}






function redirect($url, $statusCode = 302)
{
    return Yii::$app->controller->redirect($url, $statusCode);
}









function activeTextinput($form, $model, $attribute, $inputOptions = [], $fieldOptions = [])
{
    return $form->field($model, $attribute, $fieldOptions)->textInput($inputOptions);
}
