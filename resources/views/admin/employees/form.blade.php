<div class="form-grid">
    <div class="field">
        <label for="name">Name</label>
        <input class="input" id="name" name="name" value="{{ old('name', $employee->user->name ?? '') }}" required>
    </div>
    <div class="field">
        <label for="email">Email</label>
        <input class="input" id="email" type="email" name="email" value="{{ old('email', $employee->user->email ?? '') }}" required>
    </div>
    <div class="field">
        <label for="password">Password</label>
        <input class="input" id="password" type="password" name="password" {{ isset($employee) ? '' : 'required' }}>
        @isset($employee)
            <div class="muted" style="font-size: 12px; margin-top: 6px;">Kosongkan kalau password tidak diganti.</div>
        @endisset
    </div>
    <div class="field">
        <label>Employee Code</label>
        <input class="input" value="{{ $employee->employee_code ?? 'Generated automatically' }}" disabled>
    </div>
    <div class="field">
        <label for="department">Department</label>
        <select id="department" name="department">
            <option value="">Select Department</option>
            @foreach($departments as $department)
                <option value="{{ $department->name }}" @selected(old('department', $employee->department ?? '') === $department->name)>
                    {{ $department->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="position">Position</label>
        <select id="position" name="position">
            <option value="">Select Position</option>
            @foreach($positions as $position)
                <option value="{{ $position->name }}" @selected(old('position', $employee->position ?? '') === $position->name)>
                    {{ $position->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="phone">Phone</label>
        <input class="input" id="phone" name="phone" value="{{ old('phone', $employee->phone ?? '') }}">
    </div>
    <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status" required>
            <option value="active" @selected(old('status', $employee->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $employee->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
    </div>
</div>
<div class="field">
    <label for="address">Address</label>
    <textarea id="address" name="address" rows="3">{{ old('address', $employee->address ?? '') }}</textarea>
</div>
