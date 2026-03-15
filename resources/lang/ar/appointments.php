<?php

return [
    'title' => 'المواعيد',
    'new' => 'موعد جديد',
    'empty' => 'لا توجد مواعيد.',
    'pages' => [
        'create' => [
            'title' => 'حجز موعد',
            'subtitle' => 'اتبع التدفق: التخصص، الطبيب، الخدمة، التاريخ/الوقت، ثم التأكيد.',
            'back' => 'العودة إلى المواعيد',
        ],
        'edit' => [
            'title' => 'تعديل الموعد #:number',
            'subtitle' => 'حدّث التخصص والطبيب والخدمة والتاريخ/الوقت والحالة من نفس النموذج.',
            'back' => 'العودة للتفاصيل',
        ],
    ],
    'form' => [
        'steps' => [
            'specialty' => '1. اختيار التخصص',
            'doctor' => '2. اختيار الطبيب',
            'service' => '3. اختيار الخدمة',
            'datetime' => '4. اختيار التاريخ/الوقت',
            'confirm' => '5. تأكيد الحجز',
        ],
        'fields' => [
            'patient' => 'المريض',
            'status' => 'الحالة',
            'specialty' => 'الخطوة 1: التخصص',
            'doctor' => 'الخطوة 2: الطبيب',
            'service' => 'الخطوة 3: الخدمة',
            'appointment_date' => 'الخطوة 4: تاريخ الموعد',
            'appointment_time' => 'الخطوة 4: وقت الموعد',
            'notes' => 'ملاحظات',
        ],
        'placeholders' => [
            'select_patient' => 'اختر المريض',
            'select_specialty' => 'اختر التخصص',
            'select_doctor' => 'اختر الطبيب',
            'select_service' => 'اختر الخدمة',
            'select_specialty_first' => 'اختر التخصص أولا',
            'loading' => 'جار التحميل...',
            'load_failed' => 'فشل التحميل',
        ],
        'help' => [
            'specialty_filters' => 'اختر التخصص أولا لتصفية الأطباء والخدمات المتاحة.',
        ],
        'actions' => [
            'confirm_booking' => 'الخطوة 5: تأكيد الحجز',
        ],
    ],
    'filters' => [
        'specialty' => 'التخصص',
    ],
    'columns' => [
        'patient' => 'المريض',
        'specialty' => 'التخصص',
        'doctor' => 'الطبيب',
        'service' => 'الخدمة',
        'time' => 'الوقت',
    ],
    'status' => [
        'pending' => 'قيد الانتظار',
        'confirmed' => 'مؤكد',
        'checked_in' => 'تم تسجيل الحضور',
        'in_progress' => 'قيد التنفيذ',
        'completed' => 'مكتمل',
        'cancelled_by_patient' => 'ملغي من المريض',
        'cancelled_by_clinic' => 'ملغي من العيادة',
        'cancelled' => 'ملغي',
        'scheduled' => 'مجدول',
        'no_show' => 'عدم حضور',
    ],
];

