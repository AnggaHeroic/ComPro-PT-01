@php($info = \App\Support\PageContents::for('contact'))
<img src="{{ $info->image('company_logo') }}" alt="{{ $info('company_logo_alt') }}" {{ $attributes }}>
