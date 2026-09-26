@extends('admin.layouts.app')
@section('title', 'Contact page')

@section('content')

<style>
.cp-grid{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:18px;align-items:start}
@media(max-width:1050px){.cp-grid{grid-template-columns:1fr}}
.cp-f{margin-bottom:17px}
.cp-f:last-child{margin-bottom:0}
.cp-f label{display:block;font-size:13px;font-weight:600;margin-bottom:6px}
.cp-f input[type=text],.cp-f textarea{
  width:100%;font:inherit;font-size:14px;padding:10px 12px;background:#fff;
  border:1px solid var(--line);border-radius:9px;outline:0;
}
.cp-f input:focus,.cp-f textarea:focus{border-color:var(--navy)}
.cp-f textarea{resize:vertical;min-height:90px;line-height:1.55}
.cp-f .hint{font-size:12.5px;color:var(--ink-mute);margin-top:6px;line-height:1.55}
.cp-f .err{color:var(--bad);font-size:12.5px;margin-top:5px}
.cp-tick{display:flex;align-items:flex-start;gap:9px;font-size:13.5px;line-height:1.5}
.cp-tick input{margin-top:3px;flex:0 0 auto}
.cp-tick b{display:block;font-weight:600}
.cp-tick span{color:var(--ink-mute);font-size:12.5px}
.cp-live{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 0;border-bottom:1px solid var(--line);font-size:13.5px}
.cp-live:last-child{border-bottom:0}
.cp-live b{font-weight:600}
.cp-live span{color:var(--ink-mute);text-align:right;word-break:break-word}
.cp-note{background:var(--paper);border-radius:10px;padding:13px 15px;font-size:12.5px;color:var(--ink-mute);line-height:1.65;margin-top:14px}
.cp-note b{display:block;color:var(--ink);font-size:13px;margin-bottom:3px}
</style>

<form method="POST" action="{{ route('admin.contact.update') }}">
  @csrf
  @method('PATCH')

  <div class="cp-grid">

    <div>
      <div class="panel">
        <div class="panel-head">
          <div>
            <h2>What the page says</h2>
            <div class="sub">
              Live at <a href="{{ route('contact') }}" target="_blank" rel="noopener">{{ route('contact') }}</a>
            </div>
          </div>
        </div>

        <div class="panel-body">
          <div class="cp-f">
            <label for="contact_heading">Heading</label>
            <input type="text" id="contact_heading" name="contact_heading"
                   value="{{ old('contact_heading', $settings['contact_heading'] ?? '') }}"
                   placeholder="Talk to us">
            @error('contact_heading')<div class="err">{{ $message }}</div>@enderror
          </div>

          <div class="cp-f">
            <label for="contact_intro">Opening line</label>
            <textarea id="contact_intro" name="contact_intro" style="min-height:70px"
                      placeholder="A message here reaches the same people who pack the parcels.">{{ old('contact_intro', $settings['contact_intro'] ?? '') }}</textarea>
            @error('contact_intro')<div class="err">{{ $message }}</div>@enderror
          </div>

          <div class="cp-f">
            <label for="contact_reply_time">How fast you answer</label>
            <input type="text" id="contact_reply_time" name="contact_reply_time"
                   value="{{ old('contact_reply_time', $settings['contact_reply_time'] ?? '') }}"
                   placeholder="We usually reply the same day.">
            <div class="hint">Shown above the form and again after someone sends a message. Promise something you can keep.</div>
            @error('contact_reply_time')<div class="err">{{ $message }}</div>@enderror
          </div>

          <div class="cp-f">
            <label for="contact_hours">Opening hours</label>
            <textarea id="contact_hours" name="contact_hours"
                      placeholder="Saturday to Thursday — 10am to 8pm&#10;Friday — Closed">{{ old('contact_hours', $settings['contact_hours'] ?? '') }}</textarea>
            <div class="hint">One line per row. Put a dash between the day and the time and they line up in two columns.</div>
            @error('contact_hours')<div class="err">{{ $message }}</div>@enderror
          </div>

          <div class="cp-f">
            <label for="contact_topics">Subjects in the dropdown</label>
            <textarea id="contact_topics" name="contact_topics"
                      placeholder="Question about an order&#10;Is this product genuine?&#10;Delivery or payment">{{ old('contact_topics', $settings['contact_topics'] ?? '') }}</textarea>
            <div class="hint">One per line. Leave empty and a sensible set is used. Keeping these short is what lets you see at a glance what people keep asking about.</div>
            @error('contact_topics')<div class="err">{{ $message }}</div>@enderror
          </div>

          <div class="cp-f">
            <label for="contact_map">Map</label>
            <textarea id="contact_map" name="contact_map" style="min-height:70px"
                      placeholder="Paste the embed code from Google Maps">{{ old('contact_map', $settings['contact_map'] ?? '') }}</textarea>
            <div class="hint">
              Google Maps → find your shop → Share → <b>Embed a map</b> → Copy HTML. Paste the whole
              thing here; the link is taken out of it for you. Only Google Maps links are accepted.
            </div>
            @error('contact_map')<div class="err">{{ $message }}</div>@enderror
          </div>

          <div style="margin-top:20px">
            <button class="btn btn-navy">Save contact page</button>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="panel">
        <div class="panel-head"><div><h2>The form</h2></div></div>
        <div class="panel-body">
          <label class="cp-tick">
            <input type="hidden" name="contact_form_enabled" value="0">
            <input type="checkbox" name="contact_form_enabled" value="1"
                   @checked(old('contact_form_enabled', $settings['contact_form_enabled'] ?? '1') == '1')>
            <span>
              <b>Accept messages</b>
              <span>Off leaves the page and your phone number up, but takes the form away.</span>
            </span>
          </label>

          <div class="cp-note">
            <b>Spam is handled already.</b>
            The form carries a hidden field bots fill in and people never see, and it ignores
            anything sent within three seconds of the page loading. Whatever still gets through
            goes to the Spam tab, not your inbox.
          </div>
        </div>
      </div>

      <div class="panel" style="margin-top:18px">
        <div class="panel-head">
          <div>
            <h2>Details shown</h2>
            <div class="sub">These come from Settings, so they match the header and footer.</div>
          </div>
        </div>
        <div class="panel-body">
          <div class="cp-live"><b>Phone</b><span>{{ $page->phone() ?: 'Not set' }}</span></div>
          <div class="cp-live"><b>WhatsApp</b><span>{{ $settings['store_whatsapp'] ?? ($page->phone() ? 'Using phone number' : 'Not set') }}</span></div>
          <div class="cp-live"><b>Email</b><span>{{ $page->email() ?: 'Not set' }}</span></div>
          <div class="cp-live"><b>Address</b><span>{{ $page->address() ?: 'Not set' }}</span></div>

          <div class="cp-note">
            Change any of these in
            <a href="{{ route('admin.settings.edit') }}">Settings</a> and they update on the contact
            page, the header and the footer together — there is only one copy of each.
          </div>
        </div>
      </div>
    </div>

  </div>
</form>

@endsection
