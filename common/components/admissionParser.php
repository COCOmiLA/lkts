<?php

namespace common\components;

use common\modules\abiturient\modules\admission\models\ListChanceHeader;
use common\modules\abiturient\modules\admission\models\ListChanceRow;
use common\modules\abiturient\modules\admission\models\ListCompetitionHeader;
use common\modules\abiturient\modules\admission\models\ListCompetitionRow;
use Yii;
use yii\helpers\FileHelper;

class admissionParser extends \yii\base\Component
{
    const FILE_PATH = "@storage/web/lists";

    public function loadFiles()
    {
        $basePath = FileHelper::normalizePath(Yii::getAlias(self::FILE_PATH));
        foreach (glob($basePath . '/*.*') as $file) {
            echo $file;
            if (filesize($file) == 0) {
                continue;
            }
            if (strstr($file, 'Report4-')) {
                $this->loadCompetition($file, filectime($file));
            }
            if (strstr($file, 'Report5-')) {
                $this->loadChance($file, filectime($file));
            }
        }
    }

    public function loadChance($filename, $created_at)
    {
        $xml = $this->loadData($filename);
        $existing_chance = ListChanceHeader::findOne([
            'campaign_code' => (int)$xml->Header->IdPK,
            'speciality_code' => (int)trim((string)$xml->Header->IdDirection),
            'learnform_code' =>  (int)$xml->Header->IdForm,
            'filename' => $filename,
        ]);
        if ($existing_chance != null) {
            if ($existing_chance->updated_at < $created_at) {
                $this->parseChance($xml, $filename, $existing_chance);
            }
        } else {
            $this->parseChance($xml, $filename);
        }
    }

    public function parseChance($xml, $filename, $existing_chance = null)
    {
        if ($existing_chance != null) {
            ListChanceRow::deleteAll(['chance_list_id' => $existing_chance->id]);
            $existing_chance->delete();
        }

        $header = new ListChanceHeader();
        $header->campaign_code = (int)$xml->Header->IdPK;
        $header->date = (string)$xml->Header->Date;
        $header->speciality = (string)$xml->Header->Direction;
        $header->speciality_code = (int)trim((string)$xml->Header->IdDirection);
        $header->learnForm = (string)$xml->Header->form;
        $header->learnform_code = (int)$xml->Header->IdForm;
        $header->admission_phase = (string)$xml->Header->phase;
        $header->taken_percent = (string)$xml->Header->percent;
        $header->crimea_count = (string)$xml->Header->krim;
        $header->special_count = (string)$xml->Header->osob;
        $header->target_count = (string)$xml->Header->celev;
        $header->competition_count = (string)$xml->Header->contest;
        $header->total_count = (string)$xml->Header->itog;
        $header->filename = $filename;
        $header->save();

        foreach ($xml->TableData->Row as $xml_row) {
            $row = new ListChanceRow();
            $row->user_guid = (string)$xml_row->AbitCode;
            $row->group_code = (int)$xml_row->IdKonkurs;
            $row->chance_list_id = $header->id;
            $row->row_number = (string)$xml_row->Column1;
            $row->abit_regnumber = (string)$xml_row->Column2;
            $row->fio = (string)$xml_row->Column3;
            $row->speciality_priority = (string)$xml_row->Column4;
            $row->exam_points = (string)$xml_row->Column6;
            $row->id_points = (string)$xml_row->Column7;
            $row->total_points = (string)$xml_row->Column5;
            $row->agreement = (string)$xml_row->Agree;
            $row->special = (string)$xml_row->Column8;
            $row->abiturient_state = (string)$xml_row->Column9;
            $row->save();
        }
        echo $filename . ' loaded' . PHP_EOL;
    }

    public function loadCompetition($filename, $created_at)
    {
        $xml = $this->loadData($filename);
        $existing_competition = ListCompetitionHeader::findOne([
            'campaign_code' => (int)$xml->Header->IdPK,
            'speciality_system_code' => (int)trim((string)$xml->Header->IdDirection),
            'finance_code' => (int)$xml->Header->IdFinans,
            'learnform_code' =>  (int)$xml->Header->IdForm,
            'filename' => $filename,
        ]);
        if ($existing_competition != null) {
            if ($existing_competition->updated_at < $created_at) {
                $this->parseCompetition($xml, $filename, $existing_competition);
            }
        } else {
            $this->parseCompetition($xml, $filename);
        }
    }

    public function parseCompetition($xml, $filename, $existing_competition = null)
    {
        if ($existing_competition != null) {
            ListCompetitionRow::deleteAll(['competition_list_id' => $existing_competition->id]);
            $existing_competition->delete();
        }

        $header = new ListCompetitionHeader();
        $header->campaign_code = (int)$xml->Header->IdPK;
        $header->speciality_system_code = (int)trim((string)$xml->Header->IdDirection);
        $header->date = (string)$xml->Header->Date[0];
        $header->qualification = (string)$xml->Header->Qualification;
        $header->learnForm = (string)$xml->Header->form;
        $header->learnform_code = (int)$xml->Header->IdForm;
        $header->financeForm = (string)$xml->Header->finans;
        $header->finance_code = (int)$xml->Header->IdFinans;
        $header->institute = (string)$xml->Header->institute;
        $header->speciality = (string)$xml->Header->Direction;
        $header->crimea_count = (string)$xml->Header->krim;
        $header->special_count = (string)$xml->Header->osob;
        $header->target_count = (string)$xml->Header->celev;
        $header->competition_count = (string)$xml->Header->contest;
        $header->total_count = (string)$xml->Header->itog;
        $header->exam1 = (string)$xml->Header->exam1;
        $header->exam1code = (string)$xml->Header->exam1code;
        $header->exam2 = (string)$xml->Header->exam2;
        $header->exam2code = (string)$xml->Header->exam2code;
        $header->exam3 = (string)$xml->Header->exam3;
        $header->exam3code = (string)$xml->Header->exam3code;

        $header->speciality_code = (string)$xml->Header->SpecialityCode;
        $header->filename = $filename;
        $header->save();

        foreach ($xml->TableData->Row as $xml_row) {
            $row = new ListCompetitionRow();
            $row->user_guid = (string)$xml_row->AbitCode;
            $row->group_code = (int)$xml_row->IdKonkurs;
            $row->competition_list_id = $header->id;
            $row->row_number = (string)$xml_row->Column1;
            $row->abit_regnumber = (string)$xml_row->Column2;
            $row->fio = (string)$xml_row->Column3;
            $row->total_points = (string)$xml_row->Column4;
            $row->total_exam_points = (string)$xml_row->Column5;
            $row->exam1_points = (string)$xml_row->Column6;
            $row->exam2_points = (string)$xml_row->Column7;
            $row->exam3_points = (string)$xml_row->Column8;
            $row->id_points = (string)$xml_row->Column9;
            $row->speciality_priority = (string)$xml_row->Column10;
            $row->have_original = (string)$xml_row->Column11;
            $row->admission_condition = (string)$xml_row->Column12;
            $row->need_dormitory = (string)$xml_row->Column13;
            $row->abit_state = (string)$xml_row->Column14;
            $row->abit_code = (string)$xml_row->AbiturientCode;
            $row->save();
        }
        echo $filename . ' loaded' . PHP_EOL;
    }

    protected function loadData($name)
    {
        $xml = $this->buildXmlFromFile($name);
        return $xml;
    }

    protected function buildXmlFromFile($filename)
    {
        $xml = simplexml_load_file($filename);
        return $xml;
    }
}
