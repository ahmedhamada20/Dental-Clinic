<?php

return [
    'title' => 'التخصصات الطبية',
    'create_title' => 'إنشاء تخصص',
    'edit_title' => 'تعديل التخصص',
    'show_title' => 'تفاصيل التخصص',

    'columns' => [
        'id' => '#',
        'name' => 'الاسم',
        'description' => 'الوصف',
        'doctors' => 'الأطباء',
        'categories' => 'الفئات',
        'status' => 'الحالة',
        'actions' => 'الإجراءات',
    ],

    'fields' => [
        'name' => 'اسم التخصص',
        'description' => 'الوصف',
        'icon' => 'الأيقونة (اختياري)',
        'is_active' => 'نشط',
    ],

    'placeholders' => [
        'search' => 'البحث حسب الاسم أو الوصف',
        'icon' => 'bi bi-heart-pulse',
        'select_specialty' => 'اختر التخصص',
    ],

    'filters' => [
        'all_statuses' => 'الكل',
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ],

    'actions' => [
        'create' => 'إنشاء تخصص',
        'edit' => 'تعديل',
        'view_doctors' => 'عرض الأطباء',
        'delete' => 'حذف',
        'activate' => 'تفعيل',
        'deactivate' => 'تعطيل',
        'new' => 'تخصص جديد',
        'add_doctor' => 'إضافة الطبيب للتخصص',
        'back_to_list' => 'العودة إلى قائمة التخصصات',
        'filter' => 'تصفية',
        'search' => 'بحث',
        'cancel' => 'إلغاء',
    ],

    'messages' => [
        'created' => 'تم إنشاء التخصص بنجاح.',
        'updated' => 'تم تحديث التخصص بنجاح.',
        'deleted' => 'تم حذف التخصص بنجاح.',
        'activated' => 'تم تفعيل التخصص بنجاح.',
        'deactivated' => 'تم تعطيل التخصص بنجاح.',
        'not_found' => 'التخصص غير موجود.',
        'empty' => 'لم يتم العثور على أي تخصصات.',
        'doctor_already_in_specialty' => 'الطبيب المحدد مرتبط بالفعل بنفس التخصص.',
    ],

    'doctors' => [
        'list_title' => 'أطباء هذا التخصص',
        'assign_title' => 'إضافة طبيب إلى هذا التخصص',
        'select_doctor' => 'اختيار الطبيب',
        'select_doctor_placeholder' => 'اختر طبيباً',
        'current_specialty' => 'التخصص الحالي: :name',
        'no_available_doctors' => 'لا يوجد أطباء متاحون للإضافة حالياً.',
        'empty' => 'لا يوجد أطباء مسجلون في هذا التخصص بعد.',
        'columns' => [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'phone' => 'الهاتف',
            'status' => 'الحالة',
        ],
    ],

    'status' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
    ],
];

