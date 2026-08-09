<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Submit a Complaint') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:640px;">
    <div class="card shadow-sm" style="border-radius:16px;border:none;">
        <div class="card-header" style="background:#dc3545;color:#fff;border-radius:16px 16px 0 0;">
            <h4 class="mb-0">{{ __('Submit a Complaint') }}</h4>
        </div>
        <div class="card-body p-4">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('complaints.public.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Complainant Name') }} *</label>
                        <input type="text" name="complainant_name" value="{{ old('complainant_name') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Phone') }}</label>
                        <input type="text" name="complainant_phone" value="{{ old('complainant_phone') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Email') }}</label>
                        <input type="email" name="complainant_email" value="{{ old('complainant_email') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Category') }} *</label>
                        <select name="category" class="form-select" required>
                            @foreach(['missed_collection','damaged_container','illegal_dumping','odor','noise','hazardous_waste','other'] as $c)
                                <option value="{{ $c }}" {{ old('category')===$c?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$c)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Subject') }} *</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Description') }} *</label>
                        <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Address / Location') }}</label>
                        <input type="text" name="address" value="{{ old('address') }}" class="form-control">
                    </div>
                </div>
                {{-- Priority defaults to "medium" server-side for public submissions --}}
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-danger px-4">
                        {{ __('Submit Complaint') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
