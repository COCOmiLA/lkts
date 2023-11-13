<?php


namespace common\modules\student\components\grade\models;


class GradeTableRenderer
{
    public static function getFormattedRowsForTerm($rows, $term)
    {
        $j = 0;
        $tableArray = [];
        foreach ($rows as $grade_value_inner) {
            if ($grade_value_inner->Term == $term) {
                $date = date("d.m.Y", strtotime($grade_value_inner->Date));
                $date = ($date != '01.01.0001') ? $date : '';
                $tableArray[$j]['Date'] = $date;
                $tableArray[$j]['Mark'] = $grade_value_inner->Mark;
                $tableArray[$j]['Credits'] = $grade_value_inner->Credits;

                if (is_string($grade_value_inner->Subject)) {
                    $tableArray[$j]['Subject'] = $grade_value_inner->Subject;
                } elseif (
                    is_object($grade_value_inner->Subject) &&
                    isset($grade_value_inner->Subject->ReferenceName)
                ) {
                    $tableArray[$j]['Subject'] = $grade_value_inner->Subject->ReferenceName;
                }
                $tableArray[$j]['TotalLoad'] = $grade_value_inner->TotalLoad;
                $tableArray[$j]['TypeOfTheControl'] = $grade_value_inner->TypeOfTheControl;
                if (isset($grade_value_inner->Children)) {
                    $tableArray[$j]['Children'] = self::getFormattedRowsForTerm($grade_value_inner->Children, $term);
                }

                $j++;
            }
        }
        return $tableArray;
    }

    public static function renderTableContent($tableArray, $index_prefix = '')
    {
        ?>
        <?php foreach ($tableArray as $row_index => $row): ?>
        <?php
        $is_child = !empty(trim((string)$index_prefix));
        $row_index_prefix = $row_index + 1;
        $padding_value = 8;
        $fz_value = 16;
        if ($is_child) {
            $level = count(explode($index_prefix, '.'));
            
            $fz_value -= $level * 2;
            if ($fz_value < 9) {
                $fz_value = 9;
            }
        }
        $full_prefix = trim("{$index_prefix}.{$row_index_prefix}", '.'); ?>
        <tr>
            <td <?php echo(!$is_child ? 'style="font-weight: bolder;"' : 'style="border-top: 0;padding-left: ' . $padding_value . 'px;font-size: ' . $fz_value . 'px;padding-top: 3px;"') ?>><?php echo $full_prefix ?></td>
            <td <?php echo(!$is_child ? 'style="font-weight: bolder;"' : 'style="border-top: 0;padding-left: ' . $padding_value . 'px;font-size: ' . $fz_value . 'px;padding-top: 3px;"') ?>
                    class="text-left"><?php echo $row['Subject'] ?></td>
            <td <?php echo(!$is_child ? 'style="font-weight: bolder;"' : 'style="border-top: 0;padding-left: ' . $padding_value . 'px;font-size: ' . $fz_value . 'px;padding-top: 3px;"') ?>><?php echo $row['TypeOfTheControl'] ?></td>
            <td <?php echo(!$is_child ? 'style="font-weight: bolder;"' : 'style="border-top: 0;padding-left: ' . $padding_value . 'px;font-size: ' . $fz_value . 'px;padding-top: 3px;"') ?>><?php echo $row['Mark'] ?></td>
            <td <?php echo(!$is_child ? 'style="font-weight: bolder;"' : 'style="border-top: 0;padding-left: ' . $padding_value . 'px;font-size: ' . $fz_value . 'px;padding-top: 3px;"') ?>><?php echo $row['Credits'] ?></td>
            <td <?php echo(!$is_child ? 'style="font-weight: bolder;"' : 'style="border-top: 0;padding-left: ' . $padding_value . 'px;font-size: ' . $fz_value . 'px;padding-top: 3px;"') ?>><?php echo $row['TotalLoad'] ?></td>
            <td <?php echo(!$is_child ? 'style="font-weight: bolder;"' : 'style="border-top: 0;padding-left: ' . $padding_value . 'px;font-size: ' . $fz_value . 'px;padding-top: 3px;"') ?>><?php echo $row['Date'] ?></td>
        </tr>
        <?php if (isset($row['Children'])): ?>
            <?php self::renderTableContent($row['Children'], $full_prefix); ?>
        <?php endif; ?>
    <?php endforeach; ?>
        <?php
    }
}