<?php

namespace common\modules\abiturient\modules\admission\components;

use common\components\LikeQueryManager;
use common\modules\abiturient\modules\admission\models\ListCompetitionHeader;
use common\modules\abiturient\modules\admission\models\ListCompetitionRow;
use common\modules\abiturient\modules\admission\models\ListSpecialityHeader;
use common\modules\abiturient\modules\admission\models\ListSpecialityRow;
use common\modules\abiturient\modules\admission\models\ListTotalAbitBudgetCountHeader;
use common\modules\abiturient\modules\admission\models\ListTotalAbitBudgetCountRow;
use common\modules\abiturient\modules\admission\models\ListTotalAbitCountHeader;
use common\modules\abiturient\modules\admission\models\ListTotalAbitCountRow;
use Yii;
use yii\helpers\FileHelper;

class admissionLoader extends \yii\base\Component
{
    const FILE_PATH = '@storage/web/lists';

    public function loadCompetition($params = null)
    {
        if ($params == null) return null;

        if ($params['qualification'] == '0' && $params['financeForm'] == '0' && $params['learnForm'] == '0' && $params['institute'] == "ИБО") {
            $filename = "Report4-BAC-BUDJ-O-450302.xml";
        } elseif ($params['qualification'] == '0' && $params['financeForm'] == '0' && $params['learnForm'] == '0' && $params['institute'] == "МГИ") {
            $xml = $this->loadData("Report4-SPEC-BUDJ-O-230501.xml");
            $filename = "Report4-SPEC-BUDJ-O-230501.xml";
        } elseif ($params['qualification'] == '0' && $params['financeForm'] == '1' && $params['learnForm'] == '0' && $params['institute'] == "ИБО") {
            $filename = "Report4-BAC-COMM-O-450302.xml";
        } elseif ($params['qualification'] == '0' && $params['financeForm'] == '1' && $params['learnForm'] == '1' && $params['institute'] == "ИНМиН") {
            $filename = "Report4-BAC-COMM-Z-110304.xml";
        } elseif ($params['qualification'] == '1' && $params['financeForm'] == '1' && $params['learnForm'] == '0' && $params['institute'] == "ИБО") {
            $filename = "Report4-MAG-COMM-O-450402.xml";
        } elseif ($params['qualification'] == '1' && $params['financeForm'] == '1' && $params['learnForm'] == '1' && $params['institute'] == "ЭУПП") {
            $filename = "Report4-MAG-COMM-Z-380405.xml";
        } else {
            return null;
        }

        if (isset($params['spec'])) {
            $data = ListCompetitionHeader::findOne(['filename' => $filename, 'speciality' => $params['spec']]);
            if ($data != null) {
                if (isset($params["fio"]) && strlen((string)$params["fio"]) > 0) {
                    $data = ListCompetitionHeader::find()->where(['filename' => $filename, 'speciality' => $params['spec']])->with([
                        'rows' => function ($query) use ($params) {
                            $query->where([LikeQueryManager::getActionName(), 'competition_list_rows.fio', $params['fio']]);
                        }
                    ])->all();
                    $data = $data[0];
                }
                return $data;
            } else {
                $xml = $this->loadData($filename);
            }
        } else {
            $data = ListCompetitionHeader::findOne(['filename' => $filename]);
            if ($data != null) {
                if (isset($params["fio"]) && strlen((string)$params["fio"]) > 0) {
                    $data = ListCompetitionHeader::find()->where(['filename' => $filename])->with([
                        'rows' => function ($query) use ($params) {
                            $query->where([LikeQueryManager::getActionName(), 'competition_list_rows.fio', $params['fio']]);
                        }
                    ])->all();
                    $data = $data[0];
                }
                return $data;
            } else {
                $xml = $this->loadData($filename);
            }
        }

        if (isset($params['spec']) && (string)$xml->Header->Direction != $params['spec']) {
            return null;
        }
        $header = new ListCompetitionHeader();
        $header->date = (string)$xml->Header->Date[0];
        $header->qualification = (string)$xml->Header->Qualification;
        $header->learnForm = (string)$xml->Header->form;
        $header->financeForm = (string)$xml->Header->finans;
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

        if (isset($params['spec'])) {
            $data = ListCompetitionHeader::findOne(['filename' => $filename, 'speciality' => $params['spec']]);
            if ($data == null) {
                $data = ListCompetitionHeader::findOne(['filename' => $filename, 'speciality' => $params['spec']]);
                if (isset($params["fio"]) && strlen((string)$params["fio"]) > 0) {
                    $data = ListCompetitionHeader::find()->where(['filename' => $filename, 'speciality' => $params['spec']])->with([
                        'rows' => function ($query) use ($params) {
                            $query->where([LikeQueryManager::getActionName(), 'competition_list_rows.fio', $params['fio']]);
                        }
                    ])->all();
                }
                return $data;
            }
        } else {
            $data = ListCompetitionHeader::findOne(['filename' => $filename]);
            if ($data == null) {
                $data = ListCompetitionHeader::findOne(['filename' => $filename]);
                if (isset($params["fio"]) && strlen((string)$params["fio"]) > 0) {
                    $data = ListCompetitionHeader::find()->where(['filename' => $filename])->with([
                        'rows' => function ($query) use ($params) {
                            $query->where([LikeQueryManager::getActionName(), 'competition_list_rows.fio', $params['fio']]);
                        }
                    ])->all();
                }
                return $data;
            }
        }
        return $header;
    }

    public function getTotalSpec()
    {
        $data1 = $this->loadTotalAbitBudget(['qualification' => '0']);
        $data2 = $this->loadTotalAbitBudget(['qualification' => '1']);
        $data3 = $this->loadTotalAbitBudget(['qualification' => '2']);
        $data4 = $this->loadTotalAbit(['qualification' => '0']);
        $data5 = $this->loadTotalAbit(['qualification' => '1']);
        $data6 = $this->loadTotalAbit(['qualification' => '2']);

        $spec1 = array_map(function ($o) {
            return $o->getFullSpec();
        }, $data1->rows);
        $spec2 = array_map(function ($o) {
            return $o->getFullSpec();
        }, $data2->rows);
        $spec3 = array_map(function ($o) {
            return $o->getFullSpec();
        }, $data3->rows);
        $spec4 = array_map(function ($o) {
            return $o->getFullSpec();
        }, $data4->rows);
        $spec5 = array_map(function ($o) {
            return $o->getFullSpec();
        }, $data5->rows);
        $spec6 = array_map(function ($o) {
            return $o->getFullSpec();
        }, $data6->rows);

        $temp_specs = array_unique(array_merge($spec1, $spec2, $spec3, $spec4, $spec5, $spec6));
        $specs = [];
        foreach ($temp_specs as $spec) {
            $specs[$spec] = $spec;
        }
        unset($specs[' Итого:']);
        return $specs;
    }

    public function getSpecFios()
    {
        $data1 = $this->loadSpeciality(['qualification' => '0']);
        $data2 = $this->loadSpeciality(['qualification' => '1']);
        $data3 = $this->loadSpeciality(['qualification' => '2']);

        $fio1 = array_map(function ($o) {
            return $o->fio;
        }, $data1->rows);
        $fio2 = array_map(function ($o) {
            return $o->fio;
        }, $data2->rows);
        $fio3 = array_map(function ($o) {
            return $o->fio;
        }, $data3->rows);

        $temp_fio = array_unique(array_merge($fio1, $fio2, $fio3));
        $fios = [];
        foreach ($temp_fio as $fio) {
            $fios[$fio] = $fio;
        }
        return $fios;
    }

    public function getSpecCodes()
    {
        $data1 = $this->loadSpeciality(['qualification' => '0']);
        $data2 = $this->loadSpeciality(['qualification' => '1']);
        $data3 = $this->loadSpeciality(['qualification' => '2']);
        $temp_codes = [];
        for ($i = 1; $i < 5; $i++) {
            $code1 = array_map(function ($o) use ($i) {
                return $o->{"speciality_{$i}"};
            }, $data1->rows);
            $code2 = array_map(function ($o) use ($i) {
                return $o->{"speciality_{$i}"};
            }, $data2->rows);
            $code3 = array_map(function ($o) use ($i) {
                return $o->{"speciality_{$i}"};
            }, $data3->rows);
            $temp_codes[] = array_unique(array_merge($code1, $code2, $code3));
        }
        $codes_int = array_unique(call_user_func_array('array_merge', $temp_codes));
        $codes = [];
        foreach ($codes_int as $code) {
            $codes[$code] = $code;
        }
        return $codes;
    }

    public function getCompetitionInstitutes()
    {
        return [
            'ИБО' => 'ИБО',
            'МГИ' => 'МГИ',
            'ИНМиН' => 'ИНМиН',
            'ЭУПП' => 'ЭУПП',
        ];
    }

    public function getCompetitionFios()
    {
        $data1 = $this->loadCompetition(['qualification' => '0', 'financeForm' => '0', 'learnForm' => '0', 'institute' => "ИБО"]);
        $data2 = $this->loadCompetition(['qualification' => '0', 'financeForm' => '0', 'learnForm' => '0', 'institute' => "МГИ"]);
        $data3 = $this->loadCompetition(['qualification' => '0', 'financeForm' => '1', 'learnForm' => '0', 'institute' => "ИБО"]);
        $data4 = $this->loadCompetition(['qualification' => '0', 'financeForm' => '1', 'learnForm' => '1', 'institute' => "ИНМиН"]);
        $data5 = $this->loadCompetition(['qualification' => '1', 'financeForm' => '1', 'learnForm' => '0', 'institute' => "ИБО"]);
        $data6 = $this->loadCompetition(['qualification' => '1', 'financeForm' => '1', 'learnForm' => '1', 'institute' => "ЭУПП"]);


        $fio1 = array_map(function ($o) {
            return $o->fio;
        }, $data1->rows);
        $fio2 = array_map(function ($o) {
            return $o->fio;
        }, $data2->rows);
        $fio3 = array_map(function ($o) {
            return $o->fio;
        }, $data3->rows);
        $fio4 = array_map(function ($o) {
            return $o->fio;
        }, $data4->rows);
        $fio5 = array_map(function ($o) {
            return $o->fio;
        }, $data5->rows);
        $fio6 = array_map(function ($o) {
            return $o->fio;
        }, $data6->rows);

        $temp_fios = array_unique(array_merge($fio1, $fio2, $fio3, $fio4, $fio5, $fio6));
        $fios = [];
        foreach ($temp_fios as $fio) {
            $fios[$fio] = $fio;
        }

        return $fios;
    }

    public function getCompetitionSpecs()
    {
        $data1 = $this->loadCompetition(['qualification' => '0', 'financeForm' => '0', 'learnForm' => '0', 'institute' => "ИБО"]);
        $data2 = $this->loadCompetition(['qualification' => '0', 'financeForm' => '0', 'learnForm' => '0', 'institute' => "МГИ"]);
        $data3 = $this->loadCompetition(['qualification' => '0', 'financeForm' => '1', 'learnForm' => '0', 'institute' => "ИБО"]);
        $data4 = $this->loadCompetition(['qualification' => '0', 'financeForm' => '1', 'learnForm' => '1', 'institute' => "ИНМиН"]);
        $data5 = $this->loadCompetition(['qualification' => '1', 'financeForm' => '1', 'learnForm' => '0', 'institute' => "ИБО"]);
        $data6 = $this->loadCompetition(['qualification' => '1', 'financeForm' => '1', 'learnForm' => '1', 'institute' => "ЭУПП"]);


        $specs = [];
        $specs[$data1->speciality] = $data1->speciality;
        $specs[$data2->speciality] = $data2->speciality;
        $specs[$data3->speciality] = $data3->speciality;
        $specs[$data4->speciality] = $data4->speciality;
        $specs[$data5->speciality] = $data5->speciality;
        $specs[$data6->speciality] = $data6->speciality;

        return $specs;
    }

    public function loadTotalAbitBudget($params = null)
    {
        if ($params == null || ($params != null && $params['qualification'] == '0')) {
            $xml = $this->loadData("Report1A.xml");
        } elseif ($params['qualification'] == '1') {
            $xml = $this->loadData("Report1B.xml");
        } elseif ($params['qualification'] == "2") {
            $xml = $this->loadData("Report1C.xml");
        }
        $header = new ListTotalAbitBudgetCountHeader();
        $header->dateBegin = (string)$xml->Header->DateBeg;
        $header->dateEnd = (string)$xml->Header->DateEnd;
        $header->qualification = (string)$xml->Header->Qualification;
        $header->learnLevel = (string)$xml->Header->Level;
        $header->learnForm = (string)$xml->Header->Form;
        $header->financeForm = (string)$xml->Header->Finance;

        $rows = [];
        foreach ($xml->TableData->Row as $xml_row) {
            $row = new ListTotalAbitBudgetCountRow();
            $row->department = (string)$xml_row->Column1;
            $row->speciality_code = (string)$xml_row->Column2;
            $row->speciality_name = (string)$xml_row->Column3;
            $row->admission_plan = (string)$xml_row->Column4;
            $row->abiturient_count = (string)$xml_row->Column5;
            $row->doc_original_count = (string)$xml_row->Column6;
            $row->application_count = (string)$xml_row->Column7;
            if ($params == null) {
                $rows[] = $row;
            } else {
                if (isset($params["institute"]) && strlen((string)$params["institute"]) > 0 && $row->department != $params["institute"]) {
                    continue;
                }
                if (isset($params["institute"]) && strlen((string)$params["spec"]) > 0 && $row->getFullSpec() != $params["spec"]) {
                    continue;
                }
                $rows[] = $row;
            }
        }
        $header->rows = $rows;

        return $header;
    }

    public function loadTotalAbit($params = null)
    {
        if ($params == null || ($params != null && $params['qualification'] == '0')) {
            $xml = $this->loadData("Report2A.xml");
        } elseif ($params['qualification'] == '1') {
            $xml = $this->loadData("Report2B.xml");
        } elseif ($params['qualification'] == "2") {
            $xml = $this->loadData("Report2C.xml");
        }

        $header = new ListTotalAbitCountHeader();
        $header->dateBegin = (string)$xml->Header->DateBeg;
        $header->dateEnd = (string)$xml->Header->DateEnd;
        $header->qualification = (string)$xml->Header->Qualification;
        $header->learnLevel = (string)$xml->Header->Level;
        $header->learnForm = (string)$xml->Header->Form;
        $header->financeForm = (string)$xml->Header->Finance;

        $rows = [];
        foreach ($xml->TableData->Row as $xml_row) {
            $row = new ListTotalAbitCountRow();
            $row->department = (string)$xml_row->Column1;
            $row->speciality_code = (string)$xml_row->Column2;
            $row->speciality_name = (string)$xml_row->Column3;
            $row->admission_plan = (string)$xml_row->Column4;
            $row->abiturient_count = (string)$xml_row->Column5;
            $row->doc_original_count = (string)$xml_row->Column6;
            $row->application_count = (string)$xml_row->Column7;
            $row->contract_count = (string)$xml_row->Column8;
            $row->payed_contract_count = (string)$xml_row->Column9;

            if ($params == null) {
                $rows[] = $row;
            } else {
                if (isset($params["institute"]) && strlen((string)$params["institute"]) > 0 && $row->department != $params["institute"]) {
                    continue;
                }
                if (isset($params["institute"]) && strlen((string)$params["spec"]) > 0 && $row->getFullSpec() != $params["spec"]) {
                    continue;
                }
                $rows[] = $row;
            }
        }
        $header->rows = $rows;

        return $header;
    }

    public function loadSpeciality($params = null)
    {
        if ($params == null || ($params != null && $params['qualification'] == '0')) {
            $xml = $this->loadData("Report3A.xml");
        } elseif ($params['qualification'] == '1') {
            $xml = $this->loadData("Report3B.xml");
        } elseif ($params['qualification'] == "2") {
            $xml = $this->loadData("Report3C.xml");
        }

        $header = new ListSpecialityHeader();
        $header->dateBegin = (string)$xml->Header->DateBeg;
        $header->dateEnd = (string)$xml->Header->DateEnd;
        $header->qualification = (string)$xml->Header->Qualification;
        $rows = [];
        foreach ($xml->TableData->Row as $xml_row) {
            $row = new ListSpecialityRow();
            $row->row_number = (string)$xml_row->Column1;
            $row->abit_regnumber = (string)$xml_row->Column2;
            $row->fio = (string)$xml_row->Column3;
            $row->speciality_1 = preg_replace("/\r|\n/", "", (string)$xml_row->Column4);
            $row->speciality_2 = preg_replace("/\r|\n/", "", (string)$xml_row->Column5);
            $row->speciality_3 = preg_replace("/\r|\n/", "", (string)$xml_row->Column6);
            $row->speciality_4 = preg_replace("/\r|\n/", "", (string)$xml_row->Column7);
            $row->exam_form = (string)$xml_row->Column8;
            $row->admission_condition = (string)$xml_row->Column9;
            $row->have_original = (string)$xml_row->Column10;
            $row->need_dormitory = (string)$xml_row->Column11;
            if ($params == null) {
                $rows[] = $row;
            } else {
                if (
                    isset($params["code"]) && strlen((string)$params["code"]) > 0
                    && ($row->speciality_1 != $params["code"]
                        && $row->speciality_2 != $params["code"]
                        && $row->speciality_3 != $params["code"]
                        && $row->speciality_4 != $params["code"])
                ) {
                    continue;
                }
                if (isset($params["fio"]) && strlen((string)$params["fio"]) > 0 && $row->fio != $params["fio"]) {
                    continue;
                }
                $rows[] = $row;
            }
        }

        $header->rows = $rows;

        return $header;
    }

    public function loadChance()
    {
        $xml = $this->loadData("Report5-BAC-O-450302.xml");

        $header = new \common\modules\abiturient\modules\admission\models\ListChanceHeader();
        $header->speciality = (string)$xml->Header->Direction;
        $header->date = (string)$xml->Header->Date;
        $header->admission_phase = (string)$xml->Header->phase;
        $header->taken_percent = (string)$xml->Header->percent;
        $header->crimea_count = (string)$xml->Header->krim;
        $header->special_count = (string)$xml->Header->osob;
        $header->target_count = (string)$xml->Header->celev;
        $header->competition_count = (string)$xml->Header->contest;
        $header->total_count = (string)$xml->Header->itog;

        $rows = [];
        foreach ($xml->TableData->Row as $xml_row) {
            $row = new ListCompetitionRow();
            $row->row_number = (string)$xml_row->Column1;
            $row->abit_regnumber = (string)$xml_row->Column2;
            $row->fio = (string)$xml_row->Column3;
            $row->speciality_priority = (string)$xml_row->Column4;
            $row->exam_points = (string)$xml_row->Column5;
            $row->id_points = (string)$xml_row->Column6;
            $row->total_points = (string)$xml_row->Column7;
            $row->special = (string)$xml_row->Column8;
            $row->abiturient_state = (string)$xml_row->Column9;

            $rows[] = $row;
        }

        $header->rows = $rows;

        return $header;
    }

    protected function loadData($name)
    {
        $xml = $this->buildXmlFromFile($name);
        return $xml;
    }

    protected function buildXmlFromFile($filename)
    {
        $basePath = Yii::getAlias(self::FILE_PATH);
        return simplexml_load_file(FileHelper::normalizePath("{$basePath}/{$filename}"));
    }

    public function getCodes($spec_code)
    {
        $head = ListCompetitionHeader::findOne(['speciality_code' => $spec_code]);
        if ($head != null) {
            return [$head->exam1code, $head->exam2code, $head->exam3code];
        }
        return null;
    }

    public function countPosition($totalege, $spec_code)
    {
        $head = ListCompetitionHeader::find()->where(['speciality_code' => $spec_code])->with([
            'rows' => function ($query) use ($totalege) {
                $query->where(['>', 'competition_list_rows.total_points', $totalege])
                    ->orWhere(['>', 'CHAR_LENGTH(competition_list_rows.admission_condition)', 0]);
            }
        ])->all();
        if (sizeof($head) > 0) {
            return (int)(sizeof($head[0]->rows) + 1);
        } else {
            return false;
        }
    }
}
