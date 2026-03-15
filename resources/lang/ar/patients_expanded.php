<?php

return [
    'title' => 'المرضى',
    'create_title' => 'إنشاء مريض',
    'edit_title' => 'تعديل المريض',
    'show_title' => 'تفاصيل المريض',

    'sections' => [
        'patient_profile' => 'تفاصيل ملف المريض',
        'patient_profile_description' => 'تسجيل مريض وتسجيل الملف الشخصي والسجل الطبي والجهات الاتصال والملفات الأولية في سير عمل واحد.',
        'personal_information' => 'المعلومات الشخصية',
        'patient_profile_details' => 'تفاصيل ملف المريض',
        'profile_additional_info' => 'معلومات ملف إضافية',
        'medical_history' => 'السجل الطبي',
        'emergency_contacts' => 'جهات الاتصال الطارئة',
        'medical_files' => 'الملفات الطبية',
        'not_available' => 'غير متاح',
    ],

    'columns' => [
        'id' => '#',
        'patient_code' => 'رمز المريض',
        'first_name' => 'الاسم الأول',
        'last_name' => 'اسم العائلة',
        'full_name' => 'الاسم الكامل',
        'phone' => 'الهاتف',
        'email' => 'البريد الإلكتروني',
        'status' => 'الحالة',
        'actions' => 'الإجراءات',
    ],

    'fields' => [
        'first_name' => 'الاسم الأول',
        'last_name' => 'اسم العائلة',
        'phone' => 'الهاتف',
        'alternate_phone' => 'هاتف بديل',
        'email' => 'البريد الإلكتروني',
        'gender' => 'النوع',
        'date_of_birth' => 'تاريخ الميلاد',
        'city' => 'المدينة',
        'status' => 'الحالة',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'occupation' => 'المهنة',
        'marital_status' => 'الحالة الاجتماعية',
        'preferred_language' => 'اللغة المفضلة',
        'blood_group' => 'فصيلة الدم',
        'allergies' => 'الحساسيات',
        'chronic_diseases' => 'الأمراض المزمنة',
        'current_medications' => 'الأدوية الحالية',
        'dental_history' => 'السجل السني',
        'important_alerts' => 'تنبيهات مهمة',
        'contact_name' => 'اسم جهة الاتصال',
        'contact_relation' => 'العلاقة',
        'contact_phone' => 'الهاتف',
        'contact_notes' => 'ملاحظات',
    ],

    'placeholders' => [
        'search' => 'البحث حسب الاسم أو الرمز أو الهاتف أو البريد الإلكتروني',
    ],

    'filters' => [
        'all_statuses' => 'الكل',
    ],

    'actions' => [
        'create' => 'إنشاء ملف المريض',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'view' => 'عرض',
        'cancel' => 'إلغاء',
        'back_to_patients' => 'العودة إلى المرضى',
        'save_changes' => 'حفظ التغييرات',
        'add_emergency_contact' => 'إضافة جهة اتصال طارئة',
        'add_medical_file' => 'إضافة ملف طبي',
    ],

    'messages' => [
        'created' => 'تم إنشاء المريض بنجاح.',
        'updated' => 'تم تحديث المريض بنجاح.',
        'deleted' => 'تم حذف المريض بنجاح.',
        'not_found' => 'المريض غير موجود.',
        'empty' => 'لم يتم العثور على أي مرضى.',
    ],

    'status' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ],

    'genders' => [
        'male' => 'ذكر',
        'female' => 'أنثى',
        'other' => 'أخرى',
    ],
];

