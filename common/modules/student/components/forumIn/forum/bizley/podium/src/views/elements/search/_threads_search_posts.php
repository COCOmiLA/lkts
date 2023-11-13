<?php








use yii\widgets\ListView;
use yii\widgets\Pjax;

$words = [];
$query = preg_replace('/\s+/', ' ', trim((string)$query));
$tmp = explode(' ', $query);
foreach ($tmp as $tmp) {
    if (mb_strlen((string)$tmp, 'UTF-8') > 2) {
        $words[] = $tmp;
    }
}

?>
<br>
<?php Pjax::begin();
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '/elements/search/_post',
    'viewParams' => ['words' => $words, 'type' => $type],
    'summary' => '',
    'emptyText' => $type == 'topics' ? Yii::t('podium/view', 'No matching threads can be found.') : Yii::t('podium/view', 'No matching posts can be found.'),
    'emptyTextOptions' => ['tag' => 'h3', 'class' => 'text-muted'],
    'pager' => ['options' => ['class' => 'pagination pull-right']]
]);
Pjax::end(); ?>
<br>
