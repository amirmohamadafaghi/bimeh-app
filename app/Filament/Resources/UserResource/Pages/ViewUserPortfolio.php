<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Casts\JalaliDateCast;
use App\Filament\Pages\NotificationSettingsPage;
use App\Filament\Resources\UserResource;
use App\Jobs\SendBaleMessageJob;
use App\Models\Installment;
use App\Models\InstallmentAudit;
use App\Models\Insurer;
use App\Models\NotificationLog;
use App\Models\Policy;
use App\Models\User;
use App\Support\PersianNormalizer;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;

class ViewUserPortfolio extends Page
{
    use InteractsWithRecord;

    protected static string $resource = UserResource::class;

    protected static ?string $title = 'پرونده جامع بیمه‌گذار';

    protected static string $view = 'filament.resources.user-resource.view-user-portfolio';

    public string $activeTab = 'policies';

    public string $policySearch = '';
    public string $policyStatusFilter = 'all';
    public string $policyPaymentFilter = 'all';

    public string $installmentSearch = '';
    public string $installmentStatusFilter = 'all';
    public string $installmentSort = 'due_date_asc';

    public string $historyFilter = 'all';

    public bool $isEditing = false;
    public array $editData = [
        'name'              => '',
        'national_code'     => '',
        'mobile'            => '',
        'phone'             => '',
        'birth_date'        => '',
        'policyholder_type' => 'حقیقی',
        'profile_title'     => '',
        'payment_badge'     => 'normal',
        'bale_chat_id'      => '',
        'address'           => '',
        'new_password'      => '',
    ];

    public bool $showPolicyDetailModal = false;
    public ?int $viewingPolicyId = null;

    public bool $showPolicyFormModal = false;
    public ?int $editingPolicyId = null;
    public array $policyFormData = [
        'policy_number'         => '',
        'insurer_id'            => null,
        'title'                 => 'بیمه شخص ثالث',
        'insurance_category'    => 'ثالث',
        'vehicle_name'          => '',
        'plate_number'          => '',
        'vehicle_model'         => '',
        'no_claim_years'        => '',
        'driver_no_claim_years' => '',
        'profile_title'         => '',
        'issued_at_jalali'      => '',
        'expires_at_jalali'     => '',
        'payment_type'          => 'installment',
        'total_amount'          => 0,
        'initial_payment'       => 0,
        'installments_count'    => 0,
        'description'           => '',
    ];

    public bool $showInstallmentFormModal = false;
    public ?int $editingInstallmentId = null;
    public array $installmentFormData = [
        'installment_number' => 1,
        'amount'             => 0,
        'documented_amount'  => 0,
        'due_date_jalali'    => '',
        'payment_identifier' => '',
        'status'             => 'pending',
    ];

    public function getTitle(): string|Htmlable
    {
        return '';
    }

    public function getBreadcrumbs(): array
    {
        return [
            UserResource::getUrl('index') => 'بیمه‌گذاران',
            'پرونده جامع: ' . ($this->record?->name ?? ''),
        ];
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->fillEditForm();
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['policies', 'installments', 'history'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function resetPolicyFilters(): void
    {
        $this->policySearch = '';
        $this->policyStatusFilter = 'all';
        $this->policyPaymentFilter = 'all';
    }

    public function resetInstallmentFilters(): void
    {
        $this->installmentSearch = '';
        $this->installmentStatusFilter = 'all';
        $this->installmentSort = 'due_date_asc';
    }

    protected function fillEditForm(): void
    {
        /** @var User $user */
        $user = $this->getRecord();

        $this->editData = [
            'name'              => (string) ($user->name ?? ''),
            'national_code'     => (string) ($user->national_code ?? ''),
            'mobile'            => (string) ($user->mobile ?? ''),
            'phone'             => (string) ($user->phone ?? ''),
            'birth_date'        => $user->birth_date ? JalaliDateCast::toJalali($user->birth_date) : '',
            'policyholder_type' => (string) ($user->policyholder_type ?: 'حقیقی'),
            'profile_title'     => (string) ($user->profile_title ?? ''),
            'payment_badge'     => (string) ($user->payment_badge ?: 'normal'),
            'bale_chat_id'      => (string) ($user->bale_chat_id ?? ''),
            'address'           => (string) ($user->address ?? ''),
            'new_password'      => '',
        ];
    }

    public function toggleEdit(): void
    {
        if ($this->isEditing === true) {
            $this->isEditing = false;
        } else {
            $this->isEditing = true;
            $this->fillEditForm();
        }
    }

    public function saveProfile(): void
    {
        /** @var User $user */
        $user = $this->getRecord();

        $name = PersianNormalizer::normalize((string) ($this->editData['name'] ?? ''));
        $mobile = PersianNormalizer::cleanMobile((string) ($this->editData['mobile'] ?? ''));

        if ($name === '' || $mobile === '') {
            Notification::make()
                ->title('نام بیمه‌گذار و شماره تلفن همراه الزامی است.')
                ->danger()
                ->send();

            return;
        }

        $birthDateGregorian = empty($this->editData['birth_date'])
            ? null
            : JalaliDateCast::toGregorian((string) $this->editData['birth_date']);

        $updatePayload = [
            'name'              => $name,
            'national_code'     => PersianNormalizer::cleanNationalCode((string) ($this->editData['national_code'] ?? '')) ?: null,
            'mobile'            => $mobile,
            'phone'             => PersianNormalizer::normalize((string) ($this->editData['phone'] ?? '')) ?: null,
            'birth_date'        => $birthDateGregorian,
            'policyholder_type' => $this->editData['policyholder_type'] ?? 'حقیقی',
            'profile_title'     => PersianNormalizer::normalize((string) ($this->editData['profile_title'] ?? '')) ?: null,
            'payment_badge'     => $this->editData['payment_badge'] ?? 'normal',
            'bale_chat_id'      => trim((string) ($this->editData['bale_chat_id'] ?? '')) ?: null,
            'address'           => trim((string) ($this->editData['address'] ?? '')) ?: null,
        ];

        if (empty($this->editData['new_password'])) {
            // رمز عبور تغییر نکرده است
        } else {
            $updatePayload['password'] = Hash::make((string) $this->editData['new_password']);
        }

        $user->update($updatePayload);
        $this->record = $user->fresh(['policies.insurer', 'policies.installments']);
        $this->isEditing = false;

        Notification::make()
            ->title('اطلاعات پرونده بیمه‌گذار با موفقیت بروزرسانی شد.')
            ->success()
            ->send();
    }

    public function openPolicyDetail(int $policyId): void
    {
        $this->viewingPolicyId = $policyId;
        $this->showPolicyDetailModal = true;
    }

    public function closePolicyDetail(): void
    {
        $this->showPolicyDetailModal = false;
        $this->viewingPolicyId = null;
    }

    public function openCreatePolicyModal(): void
    {
        /** @var User $user */
        $user = $this->getRecord();
        $defaultInsurerId = Insurer::first()?->id;

        $this->editingPolicyId = null;
        $this->policyFormData = [
            'policy_number'         => '',
            'insurer_id'            => $defaultInsurerId,
            'title'                 => 'بیمه شخص ثالث',
            'insurance_category'    => 'ثالث',
            'vehicle_name'          => '',
            'plate_number'          => '',
            'vehicle_model'         => '',
            'no_claim_years'        => '',
            'driver_no_claim_years' => '',
            'profile_title'         => $user->profile_title ?? '',
            'issued_at_jalali'      => JalaliDateCast::toJalali(now('Asia/Tehran')),
            'expires_at_jalali'     => JalaliDateCast::toJalali(now('Asia/Tehran')->addYear()),
            'payment_type'          => 'installment',
            'total_amount'          => 0,
            'initial_payment'       => 0,
            'installments_count'    => 0,
            'description'           => '',
        ];
        $this->showPolicyFormModal = true;
    }

    public function openEditPolicyModal(int $policyId): void
    {
        /** @var User $user */
        $user = $this->getRecord();
        $policy = Policy::where('user_id', $user->id)->findOrFail($policyId);

        $isCash = $policy->payment_type === 'cash'
            || ($policy->installments()->count() === 0 && $policy->initial_payment >= $policy->total_amount && $policy->total_amount > 0);

        $this->editingPolicyId = $policy->id;
        $this->policyFormData = [
            'policy_number'         => (string) $policy->policy_number,
            'insurer_id'            => $policy->insurer_id,
            'title'                 => (string) ($policy->title ?: 'بیمه شخص ثالث'),
            'insurance_category'    => (string) ($policy->insurance_category ?: 'ثالث'),
            'vehicle_name'          => (string) ($policy->vehicle_name ?? ''),
            'plate_number'          => (string) ($policy->plate_number ?? ''),
            'vehicle_model'         => (string) ($policy->vehicle_model ?? ''),
            'no_claim_years'        => (string) ($policy->no_claim_years ?? ''),
            'driver_no_claim_years' => (string) ($policy->driver_no_claim_years ?? ''),
            'profile_title'         => (string) ($policy->profile_title ?? ''),
            'issued_at_jalali'      => $policy->issued_at_jalali === '—' ? '' : $policy->issued_at_jalali,
            'expires_at_jalali'     => $policy->expires_at_jalali === '—' ? '' : $policy->expires_at_jalali,
            'payment_type'          => $isCash ? 'cash' : 'installment',
            'total_amount'          => (int) $policy->total_amount,
            'initial_payment'       => (int) $policy->initial_payment,
            'installments_count'    => (int) ($policy->installments_count ?: $policy->installments()->count()),
            'description'           => (string) ($policy->description ?? ''),
        ];
        $this->showPolicyFormModal = true;
    }

    public function closePolicyFormModal(): void
    {
        $this->showPolicyFormModal = false;
        $this->editingPolicyId = null;
    }

    public function savePolicy(): void
    {
        /** @var User $user */
        $user = $this->getRecord();

        $policyNumber = trim((string) ($this->policyFormData['policy_number'] ?? ''));
        if ($policyNumber === '') {
            Notification::make()->title('شماره بیمه‌نامه الزامی است.')->danger()->send();

            return;
        }

        $duplicateQuery = Policy::where('policy_number', $policyNumber);
        if (is_int($this->editingPolicyId)) {
            $duplicateQuery->whereKeyNot($this->editingPolicyId);
        }
        if ($duplicateQuery->exists()) {
            Notification::make()->title('این شماره بیمه‌نامه قبلاً در سیستم ثبت شده است.')->danger()->send();

            return;
        }

        $insurerId = $this->policyFormData['insurer_id'] ?: Insurer::firstOrCreate(['name' => 'بیمه ایران'])->id;
        $issuedJalali = PersianNormalizer::normalizeText((string) ($this->policyFormData['issued_at_jalali'] ?? ''));
        $expiresJalali = PersianNormalizer::normalizeText((string) ($this->policyFormData['expires_at_jalali'] ?? ''));

        $issuedGregorian = JalaliDateCast::toGregorian($issuedJalali) ?? now('Asia/Tehran')->toDateString();
        $expiresGregorian = JalaliDateCast::toGregorian($expiresJalali) ?? now('Asia/Tehran')->addYear()->toDateString();

        $paymentType = ($this->policyFormData['payment_type'] ?? 'installment') === 'cash' ? 'cash' : 'installment';
        $totalAmount = (int) PersianNormalizer::cleanNumber($this->policyFormData['total_amount'] ?? 0);
        $initialPayment = $paymentType === 'cash'
            ? $totalAmount
            : (int) PersianNormalizer::cleanNumber($this->policyFormData['initial_payment'] ?? 0);
        $installmentsCount = $paymentType === 'cash'
            ? 0
            : max(0, (int) PersianNormalizer::cleanNumber($this->policyFormData['installments_count'] ?? 0));

        $payload = [
            'user_id'               => $user->id,
            'insurer_id'            => $insurerId,
            'policy_number'         => $policyNumber,
            'title'                 => PersianNormalizer::normalizeText((string) ($this->policyFormData['title'] ?: 'بیمه شخص ثالث')),
            'insurance_category'    => PersianNormalizer::normalizeText((string) ($this->policyFormData['insurance_category'] ?: 'ثالث')),
            'vehicle_name'          => PersianNormalizer::normalizeText((string) ($this->policyFormData['vehicle_name'] ?? '')),
            'plate_number'          => PersianNormalizer::normalizeText((string) ($this->policyFormData['plate_number'] ?? '')),
            'vehicle_model'         => PersianNormalizer::cleanNumber($this->policyFormData['vehicle_model'] ?? ''),
            'no_claim_years'        => PersianNormalizer::cleanNumber($this->policyFormData['no_claim_years'] ?? ''),
            'driver_no_claim_years' => PersianNormalizer::cleanNumber($this->policyFormData['driver_no_claim_years'] ?? ''),
            'profile_title'         => PersianNormalizer::normalizeText((string) ($this->policyFormData['profile_title'] ?? '')),
            'issued_at'             => $issuedGregorian,
            'issued_at_jalali'      => $issuedJalali ?: null,
            'started_at'            => $issuedGregorian,
            'started_at_jalali'     => $issuedJalali ?: null,
            'expires_at'            => $expiresGregorian,
            'expires_at_jalali'     => $expiresJalali ?: null,
            'payment_type'          => $paymentType,
            'total_amount'          => $totalAmount,
            'initial_payment'       => $initialPayment,
            'paid_amount'           => $paymentType === 'cash' ? $totalAmount : $initialPayment,
            'installments_count'    => $installmentsCount,
            'description'           => $this->policyFormData['description'] ?? null,
        ];

        if (is_int($this->editingPolicyId)) {
            $policy = Policy::where('user_id', $user->id)->findOrFail($this->editingPolicyId);
            $policy->update($payload);
            $msg = 'اطلاعات بیمه‌نامه با موفقیت ویرایش شد.';
        } else {
            $policy = Policy::create($payload);
            $msg = 'بیمه‌نامه جدید با موفقیت ثبت شد.';

            if ($paymentType === 'installment' && $installmentsCount > 0) {
                $remaining = max(0, $totalAmount - $initialPayment);
                $perAmount = $installmentsCount > 0 ? (int) floor($remaining / $installmentsCount) : 0;

                for ($i = 1; $i <= $installmentsCount; $i++) {
                    $dueCarbon = Carbon::parse($issuedGregorian)->addMonths($i);
                    Installment::create([
                        'policy_id'          => $policy->id,
                        'installment_number' => $i,
                        'amount'             => $perAmount,
                        'paid_amount'        => 0,
                        'due_date'           => $dueCarbon->toDateString(),
                        'due_date_jalali'    => JalaliDateCast::toJalali($dueCarbon),
                        'profile_title'      => $policy->profile_title,
                        'status'             => $dueCarbon->isPast() ? 'overdue' : 'pending',
                        'payment_type'       => 'قسط',
                    ]);
                }
            }
        }

        $this->record = $user->fresh(['policies.insurer', 'policies.installments']);
        $this->closePolicyFormModal();

        Notification::make()->title($msg)->success()->send();
    }

    public function deletePolicy(int $policyId): void
    {
        $canDelete = (bool) (
            auth()->user()?->isAdmin() ||
            auth()->user()?->hasPermission('delete_users') ||
            auth()->user()?->hasPermission('manage_policies')
        );

        if ($canDelete === false) {
            Notification::make()->title('شما مجوز حذف بیمه‌نامه را ندارید.')->danger()->send();

            return;
        }

        /** @var User $user */
        $user = $this->getRecord();
        $policy = Policy::where('user_id', $user->id)->findOrFail($policyId);
        $policyNumber = $policy->policy_number;

        $policy->installments()->delete();
        $policy->delete();

        if ($this->viewingPolicyId === $policyId) {
            $this->closePolicyDetail();
        }

        $this->record = $user->fresh(['policies.insurer', 'policies.installments']);

        Notification::make()
            ->title("بیمه‌نامه شماره {$policyNumber} حذف شد.")
            ->success()
            ->send();
    }

    public function approveInstallment(int $installmentId): void
    {
        /** @var User $user */
        $user = $this->getRecord();

        $installment = Installment::whereHas('policy', fn ($q) => $q->where('user_id', $user->id))
            ->findOrFail($installmentId);

        $installment->update([
            'status'            => 'paid',
            'paid_amount'       => $installment->amount,
            'documented_amount' => $installment->documented_amount ?: $installment->amount,
            'paid_at'           => now(),
        ]);

        $this->record = $user->fresh(['policies.insurer', 'policies.installments']);

        Notification::make()
            ->title("قسط شماره {$installment->installment_number} تایید و وصول شد.")
            ->success()
            ->send();
    }

    public function openEditInstallmentModal(int $installmentId): void
    {
        /** @var User $user */
        $user = $this->getRecord();

        $installment = Installment::whereHas('policy', fn ($q) => $q->where('user_id', $user->id))
            ->findOrFail($installmentId);

        $this->editingInstallmentId = $installment->id;
        $this->installmentFormData = [
            'installment_number' => (int) $installment->installment_number,
            'amount'             => (int) $installment->amount,
            'documented_amount'  => (int) ($installment->documented_amount ?? 0),
            'due_date_jalali'    => $installment->due_date_jalali === '—' ? '' : $installment->due_date_jalali,
            'payment_identifier' => (string) ($installment->payment_identifier ?? ''),
            'status'             => (string) $installment->status,
        ];
        $this->showInstallmentFormModal = true;
    }

    public function closeInstallmentFormModal(): void
    {
        $this->showInstallmentFormModal = false;
        $this->editingInstallmentId = null;
    }

    public function saveInstallment(): void
    {
        if ($this->editingInstallmentId === null) {
            return;
        }

        /** @var User $user */
        $user = $this->getRecord();

        $installment = Installment::whereHas('policy', fn ($q) => $q->where('user_id', $user->id))
            ->findOrFail($this->editingInstallmentId);

        $dueJalali = PersianNormalizer::normalizeText((string) ($this->installmentFormData['due_date_jalali'] ?? ''));
        $dueGregorian = JalaliDateCast::toGregorian($dueJalali) ?? ($installment->due_date ? $installment->due_date->toDateString() : now('Asia/Tehran')->toDateString());
        $amount = (int) PersianNormalizer::cleanNumber($this->installmentFormData['amount'] ?? $installment->amount);
        $docAmount = (int) PersianNormalizer::cleanNumber($this->installmentFormData['documented_amount'] ?? 0);
        $status = (string) ($this->installmentFormData['status'] ?? 'pending');

        $installment->update([
            'installment_number' => max(1, (int) ($this->installmentFormData['installment_number'] ?? 1)),
            'amount'             => $amount,
            'documented_amount'  => $docAmount > 0 ? $docAmount : ($status === 'paid' ? $amount : null),
            'paid_amount'        => $status === 'paid' ? ($docAmount > 0 ? $docAmount : $amount) : 0,
            'due_date_jalali'    => $dueJalali ?: null,
            'due_date'           => $dueGregorian,
            'payment_identifier' => PersianNormalizer::cleanNumber($this->installmentFormData['payment_identifier'] ?? '') ?: null,
            'status'             => $status,
            'paid_at'            => $status === 'paid' ? ($installment->paid_at ?? now()) : null,
        ]);

        $this->record = $user->fresh(['policies.insurer', 'policies.installments']);
        $this->closeInstallmentFormModal();

        Notification::make()
            ->title('اطلاعات قسط با موفقیت بروزرسانی شد.')
            ->success()
            ->send();
    }

    public function retryNotification(int $logId): void
    {
        /** @var User $user */
        $user = $this->getRecord();

        $log = NotificationLog::where('user_id', $user->id)->find($logId);
        if ($log instanceof NotificationLog) {
            $log->update(['status' => 'queued', 'error_message' => null]);
            SendBaleMessageJob::dispatch($log->id);

            Notification::make()
                ->title('پیام مجدداً در صف ارسال ربات بله قرار گرفت.')
                ->success()
                ->send();
        }
    }

    protected function getViewData(): array
    {
        /** @var User $user */
        $user = $this->getRecord()->loadMissing(['policies.insurer', 'policies.installments']);
        $today = now('Asia/Tehran')->startOfDay();

        $allPolicies = $user->policies ?? collect();
        $allInstallments = $allPolicies->flatMap(function (Policy $policy) {
            return $policy->installments->each(function (Installment $inst) use ($policy): void {
                $inst->setRelation('policy', $policy);
            });
        });

        $paidFromInstallments = (int) $allInstallments->where('status', 'paid')->sum('amount');
        $paidFromCashAndInitial = (int) $allPolicies->sum(function (Policy $p): int {
            $isCash = $p->payment_type === 'cash'
                || ($p->installments->isEmpty() && $p->total_amount > 0 && ($p->initial_payment >= $p->total_amount || $p->paid_amount >= $p->total_amount));

            if ($isCash) {
                return max((int) $p->total_amount, (int) $p->paid_amount, (int) $p->initial_payment);
            }

            return (int) $p->initial_payment;
        });

        $paidInstallmentsAmount = $paidFromInstallments + $paidFromCashAndInitial;
        $overdueInstallments = $allInstallments->where('status', 'overdue');
        $overdueCount = $overdueInstallments->count();
        $overdueAmount = (int) $overdueInstallments->sum('amount');
        $pendingAmount = (int) $allInstallments->whereIn('status', ['pending', 'overdue', 'awaiting_approval'])->sum('amount');

        $policySearchTerm = PersianNormalizer::normalizeText(trim($this->policySearch));
        $filteredPolicies = $allPolicies->filter(function (Policy $policy) use ($policySearchTerm, $today): bool {
            if (strlen($policySearchTerm) > 0) {
                $haystack = PersianNormalizer::normalizeText(implode(' ', [
                    $policy->policy_number,
                    $policy->title,
                    $policy->vehicle_name,
                    $policy->plate_number,
                    $policy->insurer?->name,
                    $policy->profile_title,
                ]));
                if (str_contains(mb_strtolower($haystack), mb_strtolower($policySearchTerm)) === false) {
                    return false;
                }
            }

            $isCash = $policy->payment_type === 'cash'
                || ($policy->installments->isEmpty() && $policy->total_amount > 0);

            if ($this->policyPaymentFilter === 'cash' && $isCash === false) {
                return false;
            }
            if ($this->policyPaymentFilter === 'installment' && $isCash === true) {
                return false;
            }

            if ($this->policyStatusFilter === 'all') {
                return true;
            }

            if ($policy->expires_at === null) {
                return $this->policyStatusFilter === 'active';
            }

            $daysLeft = (int) $today->diffInDays($policy->expires_at->copy()->startOfDay(), false);

            if ($this->policyStatusFilter === 'expired' && $daysLeft >= 0) {
                return false;
            }
            if ($this->policyStatusFilter === 'expiring_soon' && ($daysLeft < 0 || $daysLeft > 30)) {
                return false;
            }
            if ($this->policyStatusFilter === 'active' && $daysLeft < 0) {
                return false;
            }

            return true;
        })->values();

        $instSearchTerm = PersianNormalizer::normalizeText(trim($this->installmentSearch));
        $filteredInstallments = $allInstallments->filter(function (Installment $inst) use ($instSearchTerm): bool {
            if ($this->installmentStatusFilter === 'all' || $inst->status === $this->installmentStatusFilter) {
                if (strlen($instSearchTerm) > 0) {
                    $haystack = PersianNormalizer::normalizeText(implode(' ', [
                        $inst->policy?->policy_number,
                        $inst->policy?->vehicle_name,
                        $inst->payment_identifier,
                        (string) $inst->amount,
                        $inst->due_date_jalali,
                    ]));

                    return str_contains(mb_strtolower($haystack), mb_strtolower($instSearchTerm));
                }

                return true;
            }

            return false;
        });

        $filteredInstallments = match ($this->installmentSort) {
            'due_date_desc' => $filteredInstallments->sortByDesc('due_date')->values(),
            'amount_desc'   => $filteredInstallments->sortByDesc('amount')->values(),
            'number_asc'    => $filteredInstallments->sortBy('installment_number')->values(),
            default         => $filteredInstallments->sortBy('due_date')->values(),
        };

        $notificationLogs = NotificationLog::where('user_id', $user->id)
            ->latest()
            ->limit(40)
            ->get();

        $installmentIds = $allInstallments->pluck('id')->all();
        $audits = empty($installmentIds)
            ? collect()
            : InstallmentAudit::with(['actor:id,name', 'installment.policy:id,policy_number'])
                ->whereIn('installment_id', $installmentIds)
                ->latest()
                ->limit(40)
                ->get();

        $viewingPolicy = $this->viewingPolicyId
            ? $allPolicies->firstWhere('id', $this->viewingPolicyId)
            : null;

        $insurers = Insurer::pluck('name', 'id')->all();

        $notificationTargetUrl = NotificationSettingsPage::getUrl(['users' => (string) $user->id]) . '#custom-message-section';

        return [
            'user'                   => $user,
            'policies'               => $filteredPolicies,
            'totalPoliciesCount'     => $allPolicies->count(),
            'allInstallments'        => $filteredInstallments,
            'totalInstallmentsCount' => $allInstallments->count(),
            'paidInstallmentsAmount' => $paidInstallmentsAmount,
            'paidFromInstallments'   => $paidFromInstallments,
            'paidFromCashAndInitial' => $paidFromCashAndInitial,
            'overdueCount'           => $overdueCount,
            'overdueAmount'          => $overdueAmount,
            'pendingAmount'          => $pendingAmount,
            'notificationLogs'       => $notificationLogs,
            'audits'                 => $audits,
            'historyTotalCount'      => $notificationLogs->count() + $audits->count(),
            'viewingPolicy'          => $viewingPolicy,
            'insurers'               => $insurers,
            'notificationTargetUrl'  => $notificationTargetUrl,
            'lastUpdatedJalali'      => JalaliDateCast::toJalali($user->updated_at, 'Y/m/d H:i'),
        ];
    }
}بل