<?php

return [
    'title' => 'فئات الخدمات',
    'create_title' => 'إنشاء فئة خدمات',
    'edit_title' => 'تعديل فئة الخدمات',

    'columns' => [
        'id' => '#',
        'medical_specialty_id' => 'التخصص',
        'name' => 'الاسم',
        'services' => 'الخدمات',
        'status' => 'الحالة',
        'sort_order' => 'ترتيب الفرز',
        'actions' => 'الإجراءات',
    ],

    'fields' => [
        'medical_specialty_id' => 'التخصص',
        'name_ar' => 'الاسم (بالعربية)',
        'name_en' => 'الاسم (بالإنجليزية)',
        'sort_order' => 'ترتيب الفرز',
        'is_active' => 'نشط',
    ],

    'placeholders' => [
        'search' => 'البحث حسب الاسم',
        'select_specialty' => 'اختر التخصص',
    ],

    'filters' => [
        'all' => 'الكل',
    ],

    'actions' => [
        'create' => 'إنشاء فئة',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'activate' => 'تفعيل',
        'deactivate' => 'تعطيل',
        'new' => 'فئة جديدة',
        'filter' => 'تصفية',
        'search' => 'بحث',
        'cancel' => 'إلغاء',
    ],

    'messages' => [
        'created' => 'تم إنشاء فئة الخدمات بنجاح.',
        'updated' => 'تم تحديث فئة الخدمات بنجاح.',
        'deleted' => 'تم حذف فئة الخدمات بنجاح.',
        'activated' => 'تم تفعيل فئة الخدمات بنجاح.',
        'deactivated' => 'تم تعطيل فئة الخدمات بنجاح.',
        'cannot_delete_with_services' => 'لا يمكن حذف الفئة التي تحتوي على خدمات. يرجى حذف الخدمات أولاً.',
        'not_found' => 'فئة الخدمات غير موجودة.',
        'empty' => 'لم يتم العثور على فئات خدمات.',
    ],

    'status' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ],
];

