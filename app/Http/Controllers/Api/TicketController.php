<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    private function isAdmin(Request $request): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }

        $profile = Profile::where('user_id', $user->id)->first();
        return $profile && $profile->role === 'admin';
    }

    // ==========================================
    // متدهای کاربر (پروفایل)
    // ==========================================

    /**
     * دریافت لیست تیکت‌های کاربر لاگین کرده
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $tickets = Ticket::where('user_id', $userId)
            ->with(['latestMessage'])
            ->withCount('messages')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'tickets' => $tickets,
        ]);
    }

    /**
     * ثبت تیکت جدید توسط کاربر
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:200',
            'category' => 'required|string|in:technical,course,account,suggestion,other',
            'priority' => 'nullable|string|in:low,medium,high',
            'message' => 'required|string|min:5|max:3000',
        ], [
            'subject.required' => 'لطفاً موضوع تیکت را وارد کنید.',
            'category.required' => 'لطفاً دسته‌بندی مشکل را انتخاب کنید.',
            'message.required' => 'لطفاً متن پیام را وارد کنید.',
            'message.min' => 'متن پیام باید حداقل ۵ کاراکتر باشد.',
        ]);

        $user = $request->user();

        $ticket = DB::transaction(function () use ($user, $validated) {
            $ticket = Ticket::create([
                'user_id' => $user->id,
                'subject' => $validated['subject'],
                'category' => $validated['category'],
                'priority' => $validated['priority'] ?? 'medium',
                'status' => 'pending',
            ]);

            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'message' => $validated['message'],
                'is_admin' => false,
            ]);

            return $ticket;
        });

        $ticket->load(['messages.user.profile', 'latestMessage']);

        return response()->json([
            'success' => true,
            'message' => 'تیکت شما با موفقیت ثبت شد و به زودی توسط پشتیبانی بررسی می‌شود.',
            'ticket' => $ticket,
        ], 201);
    }

    /**
     * نمایش جزئیات یک تیکت همراه با تاریخچه پیام‌ها
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $ticket = Ticket::with(['messages.user.profile', 'user.profile'])
            ->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'تیکت مورد نظر یافت نشد.'], 404);
        }

        if ($ticket->user_id !== $user->id && !$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
        ]);
    }

    /**
     * ارسال پاسخ به تیکت توسط کاربر
     */
    public function reply(Request $request, $id)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:2|max:3000',
        ], [
            'message.required' => 'متن پاسخ نمی‌تواند خالی باشد.',
        ]);

        $user = $request->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'تیکت یافت نشد.'], 404);
        }

        if ($ticket->user_id !== $user->id) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        if ($ticket->status === 'closed') {
            return response()->json([
                'message' => 'این تیکت بسته شده است و امکان ارسال پاسخ مجدد وجود ندارد.'
            ], 400);
        }

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'is_admin' => false,
        ]);

        // به‌روزرسانی وضعیت به "در انتظار پاسخ" و تغییر تاریخ به‌روزرسانی
        $ticket->status = 'pending';
        $ticket->touch();
        $ticket->save();

        $message->load('user.profile');

        return response()->json([
            'success' => true,
            'message' => 'پاسخ شما با موفقیت ارسال شد.',
            'ticket_message' => $message,
            'ticket' => $ticket,
        ]);
    }

    /**
     * بستن تیکت توسط کاربر
     */
    public function close(Request $request, $id)
    {
        $user = $request->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'تیکت یافت نشد.'], 404);
        }

        if ($ticket->user_id !== $user->id && !$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $ticket->status = 'closed';
        $ticket->save();

        return response()->json([
            'success' => true,
            'message' => 'تیکت با موفقیت بسته شد.',
            'ticket' => $ticket,
        ]);
    }

    // ==========================================
    // متدهای پنل ادمین
    // ==========================================

    /**
     * لیست همه تیکت‌ها برای پنل ادمین
     */
    public function adminIndex(Request $request)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $query = Ticket::with(['user.profile', 'latestMessage'])
            ->withCount('messages');

        // فیلتر وضعیت
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // فیلتر دسته‌بندی
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // فیلتر جستجو
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->orderBy('updated_at', 'desc')->get();

        // آمار تیکت‌ها
        $stats = [
            'total' => Ticket::count(),
            'pending' => Ticket::where('status', 'pending')->count(),
            'answered' => Ticket::where('status', 'answered')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
        ];

        return response()->json([
            'success' => true,
            'tickets' => $tickets,
            'stats' => $stats,
        ]);
    }

    /**
     * نمایش جزئیات تیکت در پنل ادمین
     */
    public function adminShow(Request $request, $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $ticket = Ticket::with(['messages.user.profile', 'user.profile'])
            ->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'تیکت یافت نشد.'], 404);
        }

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
        ]);
    }

    /**
     * ارسال پاسخ ادمین به تیکت
     */
    public function adminReply(Request $request, $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|min:2|max:3000',
            'status' => 'nullable|string|in:pending,answered,closed',
        ], [
            'message.required' => 'متن پاسخ نمی‌تواند خالی باشد.',
        ]);

        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['message' => 'تیکت یافت نشد.'], 404);
        }

        $adminUser = $request->user();

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $adminUser->id,
            'message' => $validated['message'],
            'is_admin' => true,
        ]);

        // وضعیت تیکت پیش‌فرض به پاسخ داده شده تغییر می‌کند
        $ticket->status = $validated['status'] ?? 'answered';
        $ticket->touch();
        $ticket->save();

        $message->load('user.profile');

        return response()->json([
            'success' => true,
            'message' => 'پاسخ شما با موفقیت برای کاربر ارسال شد.',
            'ticket_message' => $message,
            'ticket' => $ticket,
        ]);
    }

    /**
     * تغییر وضعیت تیکت توسط ادمین
     */
    public function adminUpdateStatus(Request $request, $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:pending,answered,closed',
        ]);

        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['message' => 'تیکت یافت نشد.'], 404);
        }

        $ticket->status = $validated['status'];
        $ticket->save();

        return response()->json([
            'success' => true,
            'message' => 'وضعیت تیکت تغییر یافت.',
            'ticket' => $ticket,
        ]);
    }

    /**
     * حذف تیکت توسط ادمین
     */
    public function adminDestroy(Request $request, $id)
    {
        if (!$this->isAdmin($request)) {
            return response()->json(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['message' => 'تیکت یافت نشد.'], 404);
        }

        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'تیکت با موفقیت حذف شد.',
        ]);
    }
}
