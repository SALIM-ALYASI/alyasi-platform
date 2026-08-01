@extends('admin.layouts.app')

@section('title', 'تعديل مسوّق')

@section('content')

<div class="admin-data-page">

    <div class="admin-page-header">

        <div class="admin-page-header__content">

            <span class="admin-page-header__badge">
                <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
                إدارة المسوّقين
            </span>

            <h1 class="admin-page-header__title">
                {{ $marketer->name }}
            </h1>

            <p class="admin-page-header__description">
                كود المسوّق:
                <strong style="letter-spacing: 3px; font-size: 1.1em;" dir="ltr">{{ $marketer->code }}</strong>
            </p>

        </div>

        <a href="{{ route('admin.marketers.index') }}" class="admin-primary-button">
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            العودة للقائمة
        </a>

    </div>

    <section class="admin-stats-row">

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-users" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>عدد العملاء</span>
                <strong>{{ number_format($marketer->customers->count()) }}</strong>
            </div>
        </article>

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-percent" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>نسبة العمولة الحالية</span>
                <strong>{{ number_format($marketer->currentCommissionRate(), 0) }}٪</strong>
            </div>
        </article>

        <article class="admin-stat-card">
            <div class="admin-stat-card__icon">
                <i class="fa-solid fa-sack-dollar" aria-hidden="true"></i>
            </div>

            <div class="admin-stat-card__content">
                <span>إجمالي العمولات</span>
                <strong>{{ number_format($marketer->totalCommission(), 2) }}</strong>
            </div>
        </article>

    </section>

    {{-- بيانات المسوّق --}}
    <div class="dashboard-panel">

        <div class="dashboard-panel-header">
            <div>
                <h3>بيانات المسوّق</h3>
                <p>الاسم ورقم الجوال (الكود لا يمكن تغييره بعد التوليد).</p>
            </div>
        </div>

        <form action="{{ route('admin.marketers.update', $marketer) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-grid">

                <div class="form-group">
                    <label for="name">اسم المسوّق</label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="{{ old('name', $marketer->name) }}"
                        required
                    >

                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">رقم الجوال</label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        class="form-control"
                        value="{{ old('phone', $marketer->phone) }}"
                        dir="ltr"
                        required
                    >

                    @error('phone')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="admin-primary-button">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                    حفظ التعديلات
                </button>
            </div>
        </form>

    </div>

    {{-- إضافة عميل --}}
    <div class="dashboard-panel" style="margin-top: 24px;">

        <div class="dashboard-panel-header">
            <div>
                <h3>تسجيل عميل جديد</h3>
                <p>يُحتسب مبلغ العمولة تلقائيًا حسب نسبة المسوّق الحالية عند الحفظ.</p>
            </div>
        </div>

        <form action="{{ route('admin.marketers.customers.store', $marketer) }}" method="POST">
            @csrf

            <div class="form-grid">

                <div class="form-group">
                    <label for="customer_name">اسم العميل</label>

                    <input
                        type="text"
                        id="customer_name"
                        name="customer_name"
                        class="form-control"
                        value="{{ old('customer_name') }}"
                        required
                    >

                    @error('customer_name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="customer_phone">رقم جوال العميل</label>

                    <input
                        type="text"
                        id="customer_phone"
                        name="customer_phone"
                        class="form-control"
                        value="{{ old('customer_phone') }}"
                        dir="ltr"
                        required
                    >

                    @error('customer_phone')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="deal_amount">قيمة الصفقة (اختياري)</label>

                    <input
                        type="number"
                        id="deal_amount"
                        name="deal_amount"
                        class="form-control"
                        value="{{ old('deal_amount') }}"
                        step="0.01"
                        min="0"
                        dir="ltr"
                    >

                    @error('deal_amount')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group full">
                    <label for="notes">ملاحظات (اختياري)</label>

                    <textarea
                        id="notes"
                        name="notes"
                        class="form-control"
                    >{{ old('notes') }}</textarea>
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="admin-primary-button">
                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                    إضافة العميل
                </button>
            </div>
        </form>

    </div>

    {{-- قائمة العملاء --}}
    <section class="admin-list-panel" style="margin-top: 24px;">

        <div class="admin-list-panel__header">
            <div>
                <h2>عملاء هذا المسوّق</h2>
                <p>كل العملاء اللي جابهم هذا المسوّق وعمولتهم.</p>
            </div>
        </div>

        @if ($marketer->customers->isEmpty())

            <div class="admin-empty-state">
                <div class="admin-empty-state__icon">
                    <i class="fa-solid fa-users" aria-hidden="true"></i>
                </div>

                <h3>لا يوجد عملاء بعد</h3>
                <p>سجّل أول عميل جابه هذا المسوّق من النموذج أعلاه.</p>
            </div>

        @else

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>العميل</th>
                            <th>الجوال</th>
                            <th>قيمة الصفقة</th>
                            <th>نسبة العمولة</th>
                            <th>مبلغ العمولة</th>
                            <th>التاريخ</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($marketer->customers as $customer)
                            <tr>
                                <td>{{ $customer->customer_name }}</td>
                                <td dir="ltr" style="text-align: right;">{{ $customer->customer_phone }}</td>
                                <td>{{ $customer->deal_amount !== null ? number_format($customer->deal_amount, 2) : '—' }}</td>
                                <td>{{ number_format($customer->commission_rate, 0) }}٪</td>
                                <td>{{ $customer->commission_amount !== null ? number_format($customer->commission_amount, 2) : '—' }}</td>
                                <td>{{ $customer->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <form
                                        action="{{ route('admin.marketers.customers.destroy', [$marketer, $customer]) }}"
                                        method="POST"
                                        onsubmit="return confirm('هل تريد حذف هذا العميل من سجل المسوّق؟');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="admin-action-button admin-action-button--delete"
                                            title="حذف"
                                            aria-label="حذف العميل"
                                        >
                                            <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @endif

    </section>

</div>

@endsection
