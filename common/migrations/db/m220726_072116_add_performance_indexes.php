<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m220726_072116_add_performance_indexes extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        
        $this->createIndex(
            'idx-attachment_type-system_type',
            '{{%attachment_type}}',
            'system_type'
        );
        
        $this->createIndex(
            'idx-campaign_info-campaign-level-source-form-detail-category',
            '{{%campaign_info}}',
            [
                'campaign_id',
                'education_level_ref_id',
                'education_source_ref_id',
                'education_form_ref_id',
                'detail_group_ref_id',
                'admission_category_id',
                'archive',
            ]
        );
        
        $this->createIndex(
            'idx-campaign_info-campaign-source-form-detail-category',
            '{{%campaign_info}}',
            [
                'campaign_id',
                'education_source_ref_id',
                'education_form_ref_id',
                'detail_group_ref_id',
                'admission_category_id',
                'archive',
            ]
        );

        
        $this->createIndex(
            'idx-education_level_reference_type-reference_uid',
            '{{%education_level_reference_type}}',
            'reference_uid'
        );
        $this->createIndex(
            'idx-education_source_reference_type-reference_uid',
            '{{%education_source_reference_type}}',
            'reference_uid'
        );
        $this->createIndex(
            'idx-education_form_reference_type-reference_uid',
            '{{%education_form_reference_type}}',
            'reference_uid'
        );
        $this->createIndex(
            'idx-detail_group_reference_type-reference_uid',
            '{{%detail_group_reference_type}}',
            'reference_uid'
        );
        $this->createIndex(
            'idx-admission_campaign_reference_type-reference_uid',
            '{{%admission_campaign_reference_type}}',
            'reference_uid'
        );

        
        $this->createIndex(
            'idx-dictionary_admission_categories-ref_key',
            '{{%dictionary_admission_categories}}',
            'ref_key'
        );

        
        $this->createIndex(
            'idx-speciality-receipt-archive-source-form-detail-campaign',
            '{{%dictionary_speciality}}',
            [
                'receipt_allowed',
                'archive',
                'education_source_ref_id',
                'education_form_ref_id',
                'detail_group_ref_id',
                'campaign_ref_id',
            ]
        );
        
        $this->createIndex(
            'idx-files-upload_name-content_hash',
            '{{%files}}',
            ['upload_name', 'content_hash']
        );
        
        $this->createIndex(
            'idx-att_type-campaign-using-hidden-system-related-required',
            '{{%attachment_type}}',
            [
                'admission_campaign_ref_id',
                'is_using',
                'hidden',
                'system_type',
                'related_entity',
                'required',
            ]
        );
        
        $this->createIndex(
            'idx-attachment_type-hidden-system-type',
            '{{%attachment_type}}',
            ['hidden', 'system_type']
        );
        
        $this->createIndex(
            'idx-attachment-attachment_type_id-deleted',
            '{{%attachment}}',
            ['attachment_type_id', 'deleted']
        );
    }

    


    public function safeDown()
    {
        
        $this->dropIndex(
            'idx-attachment_type-system_type',
            '{{%attachment_type}}'
        );
        
        $this->dropIndex(
            'idx-campaign_info-campaign-level-source-form-detail-category',
            '{{%campaign_info}}'
        );
        
        $this->dropIndex(
            'idx-campaign_info-campaign-source-form-detail-category',
            '{{%campaign_info}}'
        );
        
        $this->dropIndex(
            'idx-education_level_reference_type-reference_uid',
            '{{%education_level_reference_type}}'
        );
        $this->dropIndex(
            'idx-education_source_reference_type-reference_uid',
            '{{%education_source_reference_type}}'
        );
        $this->dropIndex(
            'idx-education_form_reference_type-reference_uid',
            '{{%education_form_reference_type}}'
        );
        $this->dropIndex(
            'idx-detail_group_reference_type-reference_uid',
            '{{%detail_group_reference_type}}'
        );
        $this->dropIndex(
            'idx-admission_campaign_reference_type-reference_uid',
            '{{%admission_campaign_reference_type}}'
        );
        
        $this->dropIndex(
            'idx-dictionary_admission_categories-ref_key',
            '{{%dictionary_admission_categories}}'
        );
        
        $this->dropIndex(
            'idx-speciality-receipt-archive-source-form-detail-campaign',
            '{{%dictionary_speciality}}'
        );
        
        $this->dropIndex(
            'idx-files-upload_name-content_hash',
            '{{%files}}'
        );
        
        $this->dropIndex(
            'idx-att_type-campaign-using-hidden-system-related-required',
            '{{%attachment_type}}'
        );
        
        $this->dropIndex(
            'idx-attachment_type-hidden-system-type',
            '{{%attachment_type}}'
        );
        
        $this->dropIndex(
            'idx-attachment-attachment_type_id-deleted',
            '{{%attachment}}'
        );

    }
}
