/**
 * OrderZap Notifications – Admin React App
 */

const { useState, useEffect, useCallback, createContext, useContext } = React;

// ─── API Helper ──────────────────────────────────────────────────────────────

const api = {
  base:  window.wcWan?.apiBase  || '/wp-json/wc-wan/v1',
  nonce: window.wcWan?.nonce    || '',

  async get(path) {
    const res = await fetch(this.base + path, {
      headers: { 'X-WP-Nonce': this.nonce },
    });
    if (!res.ok) throw new Error(await res.text());
    return res.json();
  },

  async post(path, body) {
    const res = await fetch(this.base + path, {
      method: 'POST',
      headers: { 'X-WP-Nonce': this.nonce, 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    if (!res.ok) throw new Error(await res.text());
    return res.json();
  },
};

// ─── Toast ────────────────────────────────────────────────────────────────────

const ToastCtx = createContext(null);
const useToast = () => useContext(ToastCtx);

function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([]);
  const add = useCallback((msg, type = 'success') => {
    const id = Date.now();
    setToasts(t => [...t, { id, msg, type }]);
    setTimeout(() => setToasts(t => t.filter(x => x.id !== id)), 4000);
  }, []);
  return (
    <ToastCtx.Provider value={add}>
      {children}
      <div style={{ position:'fixed', bottom:20, right:20, zIndex:99999, display:'flex', flexDirection:'column', gap:8 }}>
        {toasts.map(t => (
          <div key={t.id} style={{
            padding:'12px 18px', borderRadius:12, fontSize:14, fontWeight:500,
            color:'#fff', boxShadow:'0 4px 16px rgba(0,0,0,.18)',
            background: t.type === 'error' ? '#ef4444' : '#22c55e',
          }}>
            {t.msg}
          </div>
        ))}
      </div>
    </ToastCtx.Provider>
  );
}

// ─── Icons ───────────────────────────────────────────────────────────────────

const Icon = ({ name, cls = 'w-5 h-5' }) => {
  const p = { className: cls, fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' };
  const icons = {
    dashboard: <svg {...p}><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>,
    settings:  <svg {...p}><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>,
    template:  <svg {...p}><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>,
    logs:      <svg {...p}><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>,
    whatsapp:  <svg className={cls} fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>,
    check:     <svg {...p}><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg>,
    x:         <svg {...p}><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /></svg>,
    send:      <svg {...p}><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>,
    refresh:   <svg {...p}><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>,
    external:  <svg {...p}><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>,
  };
  return icons[name] || null;
};

// ─── Toggle ──────────────────────────────────────────────────────────────────

function Toggle({ checked, onChange }) {
  return (
    <button type="button" role="switch" aria-checked={checked} onClick={() => onChange(!checked)}
      className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 ${checked ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'}`}>
      <span className={`inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform ${checked ? 'translate-x-6' : 'translate-x-1'}`} />
    </button>
  );
}

// ─── Spinner ─────────────────────────────────────────────────────────────────

function Spinner({ size = 8 }) {
  return <div className={`w-${size} h-${size} border-4 border-green-500 border-t-transparent rounded-full animate-spin`} />;
}

// ─── Dashboard ────────────────────────────────────────────────────────────────

function DashboardPage() {
  const [stats,   setStats]   = useState({ total: 0, sent: 0, failed: 0 });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get('/stats').then(setStats).catch(console.error).finally(() => setLoading(false));
  }, []);

  const rate  = stats.total > 0 ? Math.round((stats.sent / stats.total) * 100) : 0;
  const cards = [
    { label: 'Total Sent',     value: stats.total,  color: 'bg-blue-50 dark:bg-blue-900/20',    text: 'text-blue-600 dark:text-blue-400',    border: 'border-blue-200 dark:border-blue-800' },
    { label: 'Delivered',      value: stats.sent,   color: 'bg-emerald-50 dark:bg-emerald-900/20', text: 'text-emerald-600 dark:text-emerald-400', border: 'border-emerald-200 dark:border-emerald-800' },
    { label: 'Failed',         value: stats.failed, color: 'bg-red-50 dark:bg-red-900/20',      text: 'text-red-600 dark:text-red-400',      border: 'border-red-200 dark:border-red-800' },
    { label: 'Success Rate',   value: `${rate}%`,   color: 'bg-purple-50 dark:bg-purple-900/20', text: 'text-purple-600 dark:text-purple-400', border: 'border-purple-200 dark:border-purple-800' },
  ];

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
          <span className="text-green-500"><Icon name="whatsapp" cls="w-7 h-7" /></span>
          OrderZap Notifications
        </h1>
        <p className="text-gray-500 dark:text-gray-400 mt-1">Monitor and manage your WooCommerce WhatsApp notifications</p>
      </div>

      {loading ? (
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
          {[...Array(4)].map((_, i) => <div key={i} className="h-28 rounded-2xl bg-gray-100 dark:bg-gray-800 animate-pulse" />)}
        </div>
      ) : (
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          {cards.map(c => (
            <div key={c.label} className={`rounded-2xl border p-5 ${c.color} ${c.border}`}>
              <p className="text-sm font-medium text-gray-500 dark:text-gray-400">{c.label}</p>
              <p className={`text-3xl font-bold mt-1 ${c.text}`}>{c.value}</p>
            </div>
          ))}
        </div>
      )}

      {!loading && stats.total > 0 && (
        <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 mb-6">
          <div className="flex justify-between mb-2">
            <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Delivery Success Rate</span>
            <span className="text-sm font-bold text-gray-900 dark:text-white">{rate}%</span>
          </div>
          <div className="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
            <div className="bg-green-500 h-2.5 rounded-full transition-all duration-500" style={{ width: `${rate}%` }} />
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        {[
          { label: 'Configure API',  desc: 'Set up your WhatsApp provider credentials', page: 'settings',  icon: 'settings'  },
          { label: 'Edit Templates', desc: 'Customize your notification message templates', page: 'templates', icon: 'template' },
          { label: 'View Logs',      desc: 'See all sent and failed messages', page: 'logs',      icon: 'logs'     },
        ].map(item => (
          <button key={item.label}
            onClick={() => window.location.href = window.wcWan.adminUrl + '?page=wc-wan-' + item.page}
            className="text-left bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 hover:border-green-400 hover:shadow-md transition-all group">
            <div className="flex items-center gap-3 mb-2">
              <span className="text-green-500 group-hover:scale-110 transition-transform"><Icon name={item.icon} /></span>
              <span className="font-semibold text-gray-900 dark:text-white">{item.label}</span>
            </div>
            <p className="text-sm text-gray-500 dark:text-gray-400">{item.desc}</p>
          </button>
        ))}
      </div>
    </div>
  );
}

// ─── Settings Page ────────────────────────────────────────────────────────────

// Provider metadata: label, description, console URL, field definitions
const PROVIDERS = {
  meta: {
    label:   'Meta WhatsApp Cloud API',
    tagline: 'Official Meta Business API – recommended',
    url:     'https://developers.facebook.com/apps/',
    urlText: 'developers.facebook.com',
    badge:   'Recommended',
    fields: [
      { key: 'meta_access_token',    label: 'Access Token',                   type: 'password', placeholder: 'EAAxxxxx…' },
      { key: 'meta_phone_number_id', label: 'Phone Number ID',                type: 'text',     placeholder: '1234567890' },
      { key: 'meta_waba_id',         label: 'WhatsApp Business Account ID',   type: 'text',     placeholder: '1234567890' },
    ],
  },
  twilio: {
    label:   'Twilio WhatsApp API',
    tagline: 'Send via Twilio – great for testing',
    url:     'https://www.twilio.com/console',
    urlText: 'twilio.com/console',
    badge:   null,
    fields: [
      { key: 'twilio_account_sid',  label: 'Account SID',                  type: 'text',     placeholder: 'ACxxxxx…' },
      { key: 'twilio_auth_token',   label: 'Auth Token',                   type: 'password', placeholder: 'Your auth token' },
      { key: 'twilio_from_number',  label: 'Twilio WhatsApp Number',       type: 'text',     placeholder: '+14155238886' },
    ],
  },
};

const NOTIFICATION_EVENTS = [
  { key: 'notify_pending',       label: 'New Order (Pending)' },
  { key: 'notify_processing',    label: 'Order Processing' },
  { key: 'notify_completed',     label: 'Order Completed' },
  { key: 'notify_cancelled',     label: 'Order Cancelled' },
  { key: 'notify_refunded',      label: 'Order Refunded' },
  { key: 'notify_failed',        label: 'Order Failed' },
  { key: 'notify_on_hold',       label: 'Order On Hold' },
  { key: 'notify_customer_note', label: 'Customer Note' },
  { key: 'notify_tracking',      label: 'Tracking Update' },
];

function SettingsPage() {
  const toast = useToast();
  const [settings, setSettings] = useState({});
  const [loading,  setLoading]  = useState(true);
  const [saving,   setSaving]   = useState(false);
  const [testing,  setTesting]  = useState(false);
  const [tab,      setTab]      = useState('api');

  useEffect(() => {
    api.get('/settings').then(setSettings).catch(console.error).finally(() => setLoading(false));
  }, []);

  const set = (key, val) => setSettings(s => ({ ...s, [key]: val }));

  const save = async () => {
    setSaving(true);
    try {
      await api.post('/settings', settings);
      toast('Settings saved successfully!');
    } catch (e) {
      toast('Failed to save settings.', 'error');
    } finally {
      setSaving(false);
    }
  };

  const testMessage = async () => {
    if (!settings.test_phone) { toast('Enter a test phone number first.', 'error'); return; }
    setTesting(true);
    try {
      const r = await api.post('/test', { phone: settings.test_phone });
      r.success ? toast('Test message sent! Check your WhatsApp.') : toast('Failed: ' + r.error, 'error');
    } catch (e) {
      toast('Test failed: ' + e.message, 'error');
    } finally {
      setTesting(false);
    }
  };

  if (loading) return <div className="flex items-center justify-center h-64"><Spinner size={8} /></div>;

  const activeProvider = PROVIDERS[settings.provider] || PROVIDERS.meta;

  return (
    <div>
      {/* Header */}
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Settings</h1>
          <p className="text-gray-500 dark:text-gray-400 mt-1">Configure your WhatsApp API and notification preferences</p>
        </div>
        <div className="flex items-center gap-2 text-sm">
          <span className="text-gray-600 dark:text-gray-400">Plugin Active</span>
          <Toggle checked={!!settings.enabled} onChange={v => set('enabled', v)} />
        </div>
      </div>

      {/* Tabs */}
      <div className="flex gap-1 mb-6 bg-gray-100 dark:bg-gray-800 rounded-xl p-1 w-fit">
        {['api', 'notifications', 'advanced'].map(t => (
          <button key={t} onClick={() => setTab(t)}
            className={`px-4 py-2 rounded-lg text-sm font-medium capitalize transition-all ${tab === t ? 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'}`}>
            {t}
          </button>
        ))}
      </div>

      <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">

        {/* ── API Tab ── */}
        {tab === 'api' && (
          <div className="space-y-8 max-w-lg">

            {/* Provider selector */}
            <div>
              <p className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">WhatsApp Provider</p>
              <div className="grid grid-cols-2 gap-3">
                {Object.entries(PROVIDERS).map(([slug, info]) => {
                  const active = settings.provider === slug;
                  return (
                    <button key={slug} onClick={() => set('provider', slug)}
                      className={`relative text-left rounded-xl border-2 p-4 transition-all ${active ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'}`}>
                      {info.badge && (
                        <span className="absolute top-3 right-3 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400">
                          {info.badge}
                        </span>
                      )}
                      <p className={`font-semibold text-sm ${active ? 'text-green-700 dark:text-green-400' : 'text-gray-800 dark:text-white'}`}>{info.label}</p>
                      <p className="text-xs text-gray-500 dark:text-gray-400 mt-1 pr-16">{info.tagline}</p>
                      {/* Console link */}
                      <a href={info.url} target="_blank" rel="noopener noreferrer"
                        onClick={e => e.stopPropagation()}
                        className="inline-flex items-center gap-1 mt-2.5 text-xs text-blue-500 hover:text-blue-600 hover:underline">
                        <Icon name="external" cls="w-3 h-3" />
                        {info.urlText}
                      </a>
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Active provider fields */}
            <div>
              <p className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                {activeProvider.label} — Credentials
              </p>
              <div className="space-y-4">
                {activeProvider.fields.map(f => (
                  <div key={f.key}>
                    <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{f.label}</label>
                    <input type={f.type} value={settings[f.key] || ''} onChange={e => set(f.key, e.target.value)}
                      placeholder={f.placeholder} autoComplete="off"
                      className="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500" />
                  </div>
                ))}
              </div>
            </div>

            {/* Test message */}
            <div className="border-t border-gray-200 dark:border-gray-700 pt-6">
              <p className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Send Test Message</p>
              <div className="flex gap-3">
                <input type="tel" value={settings.test_phone || ''} onChange={e => set('test_phone', e.target.value)}
                  placeholder="+919876543210"
                  className="flex-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500" />
                <button onClick={testMessage} disabled={testing}
                  className="flex items-center gap-2 px-4 py-2 rounded-xl bg-green-500 text-white text-sm font-medium hover:bg-green-600 disabled:opacity-50 transition-colors">
                  {testing ? <Spinner size={4} /> : <Icon name="send" cls="w-4 h-4" />}
                  {testing ? 'Sending…' : 'Send Test'}
                </button>
              </div>
              <p className="text-xs text-gray-400 mt-2">Enter a phone number in E.164 format (+91XXXXXXXXXX). Save settings first.</p>
            </div>
          </div>
        )}

        {/* ── Notifications Tab ── */}
        {tab === 'notifications' && (
          <div className="max-w-lg space-y-1">
            <p className="text-sm text-gray-500 dark:text-gray-400 mb-4">Toggle which order events send a WhatsApp message to the customer.</p>
            {NOTIFICATION_EVENTS.map(ev => (
              <div key={ev.key} className="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <span className="text-sm font-medium text-gray-700 dark:text-gray-300">{ev.label}</span>
                <Toggle checked={!!settings[ev.key]} onChange={v => set(ev.key, v)} />
              </div>
            ))}
          </div>
        )}

        {/* ── Advanced Tab ── */}
        {tab === 'advanced' && (
          <div className="max-w-lg space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Log Retention (days)</label>
              <input type="number" min="1" max="365" value={settings.log_retention_days || 30}
                onChange={e => set('log_retention_days', parseInt(e.target.value))}
                className="w-32 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500" />
              <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">Logs older than this will be pruned automatically.</p>
            </div>
          </div>
        )}

        {/* Save */}
        <div className="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
          <button onClick={save} disabled={saving}
            className="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-green-500 text-white font-medium hover:bg-green-600 disabled:opacity-50 transition-colors">
            {saving ? <Spinner size={4} /> : <Icon name="check" cls="w-4 h-4" />}
            {saving ? 'Saving…' : 'Save Settings'}
          </button>
        </div>
      </div>
    </div>
  );
}

// ─── Templates Page ───────────────────────────────────────────────────────────

const TEMPLATE_VARS = ['{customer_name}','{order_id}','{order_total}','{order_status}','{order_url}','{tracking_number}','{courier_name}','{tracking_url}','{site_name}','{note_message}'];

function TemplatesPage() {
  const toast = useToast();
  const [templates, setTemplates] = useState([]);
  const [loading,   setLoading]   = useState(true);
  const [saving,    setSaving]    = useState(false);
  const [active,    setActive]    = useState(null);

  useEffect(() => {
    api.get('/templates').then(data => {
      setTemplates(data);
      if (data.length) setActive(data[0].event_slug);
    }).catch(console.error).finally(() => setLoading(false));
  }, []);

  const current    = templates.find(t => t.event_slug === active);
  const updateCurrent = (key, val) => setTemplates(ts => ts.map(t => t.event_slug === active ? { ...t, [key]: val } : t));

  const save = async () => {
    setSaving(true);
    try {
      await api.post('/templates', templates);
      toast('Templates saved!');
    } catch { toast('Failed to save.', 'error'); }
    finally { setSaving(false); }
  };

  const insertVar = (v) => {
    if (!current) return;
    const ta    = document.getElementById('wc-wan-tpl-editor');
    const start = ta.selectionStart;
    const end   = ta.selectionEnd;
    updateCurrent('template', current.template.slice(0, start) + v + current.template.slice(end));
    setTimeout(() => { ta.focus(); ta.setSelectionRange(start + v.length, start + v.length); }, 0);
  };

  if (loading) return <div className="flex items-center justify-center h-64"><Spinner size={8} /></div>;

  const previewText = current?.template
    ?.replace(/{customer_name}/g,  'Rahul Sharma')
    ?.replace(/{order_id}/g,       '1001')
    ?.replace(/{order_total}/g,    '₹1,499.00')
    ?.replace(/{order_status}/g,   'Processing')
    ?.replace(/{site_name}/g,      window.wcWan?.siteTitle || 'My Store')
    ?.replace(/{order_url}/g,      'https://example.com/my-account/orders/1001')
    ?.replace(/{tracking_number}/g,'TRK123456789')
    ?.replace(/{courier_name}/g,   'DTDC Express')
    ?.replace(/{tracking_url}/g,   'https://track.dtdc.com/TRK123456789')
    ?.replace(/{note_message}/g,   'Your order will arrive tomorrow between 10am–12pm.')
    || '';

  return (
    <div>
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Message Templates</h1>
          <p className="text-gray-500 dark:text-gray-400 mt-1">Customize notification messages for each order event</p>
        </div>
        <button onClick={save} disabled={saving}
          className="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-green-500 text-white font-medium hover:bg-green-600 disabled:opacity-50 transition-colors">
          {saving ? <Spinner size={4} /> : <Icon name="check" cls="w-4 h-4" />}
          {saving ? 'Saving…' : 'Save All'}
        </button>
      </div>

      <div className="flex gap-6">
        {/* Sidebar */}
        <div className="w-52 flex-shrink-0">
          <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            {templates.map(t => (
              <button key={t.event_slug} onClick={() => setActive(t.event_slug)}
                className={`w-full text-left px-4 py-3 text-sm border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors flex items-center justify-between ${active === t.event_slug ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'}`}>
                <span>{t.label}</span>
                <span className={`w-2 h-2 rounded-full flex-shrink-0 ${parseInt(t.is_active) ? 'bg-green-500' : 'bg-gray-300'}`} />
              </button>
            ))}
          </div>
        </div>

        {/* Editor + Preview */}
        {current && (
          <div className="flex-1 space-y-4">
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
              <div className="flex items-center justify-between mb-4">
                <h2 className="font-semibold text-gray-900 dark:text-white">{current.label}</h2>
                <div className="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                  Active
                  <Toggle checked={!!parseInt(current.is_active)} onChange={v => updateCurrent('is_active', v ? 1 : 0)} />
                </div>
              </div>
              <textarea id="wc-wan-tpl-editor" rows={10} value={current.template}
                onChange={e => updateCurrent('template', e.target.value)}
                className="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-sm text-gray-900 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-green-500 resize-y" />
              <div className="mt-3">
                <p className="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Click variable to insert at cursor:</p>
                <div className="flex flex-wrap gap-2">
                  {TEMPLATE_VARS.map(v => (
                    <button key={v} onClick={() => insertVar(v)}
                      className="px-2.5 py-1 rounded-lg text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-green-100 dark:hover:bg-green-900/30 hover:text-green-700 dark:hover:text-green-400 transition-colors">
                      {v}
                    </button>
                  ))}
                </div>
              </div>
            </div>

            {/* Preview */}
            <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
              <p className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Preview</p>
              <div className="wc-wan-bubble">
                <pre className="text-sm text-gray-800 whitespace-pre-wrap font-sans leading-relaxed">{previewText}</pre>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

// ─── Logs Page ────────────────────────────────────────────────────────────────

function LogsPage() {
  const [logs,    setLogs]    = useState({ rows: [], total: 0, pages: 1 });
  const [loading, setLoading] = useState(true);
  const [page,    setPage]    = useState(1);
  const [filter,  setFilter]  = useState('');

  const load = useCallback(() => {
    setLoading(true);
    api.get(`/logs?page=${page}&per_page=25&status=${filter}`)
      .then(setLogs).catch(console.error).finally(() => setLoading(false));
  }, [page, filter]);

  useEffect(() => { load(); }, [load]);

  const badge = s => s === 'sent'
    ? <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400"><Icon name="check" cls="w-3 h-3" />Sent</span>
    : <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400"><Icon name="x" cls="w-3 h-3" />Failed</span>;

  return (
    <div>
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Message Logs</h1>
          <p className="text-gray-500 dark:text-gray-400 mt-1">{logs.total} total entries</p>
        </div>
        <div className="flex items-center gap-3">
          <select value={filter} onChange={e => { setFilter(e.target.value); setPage(1); }}
            className="rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">All Status</option>
            <option value="sent">Sent</option>
            <option value="failed">Failed</option>
          </select>
          <button onClick={load} className="p-2 rounded-xl border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-colors">
            <Icon name="refresh" />
          </button>
        </div>
      </div>

      <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        {loading ? (
          <div className="flex items-center justify-center h-48"><Spinner size={8} /></div>
        ) : logs.rows.length === 0 ? (
          <div className="flex flex-col items-center justify-center h-48 text-gray-400">
            <Icon name="logs" cls="w-10 h-10 mb-2 opacity-30" />
            <p className="text-sm">No log entries found</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                  {['Order','Event','Recipient','Status','Message ID','Error','Time'].map(h => (
                    <th key={h} className="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">{h}</th>
                  ))}
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100 dark:divide-gray-700">
                {logs.rows.map(row => (
                  <tr key={row.id} className="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td className="px-4 py-3 font-medium text-gray-900 dark:text-white">{row.order_id || '—'}</td>
                    <td className="px-4 py-3 capitalize text-gray-700 dark:text-gray-300">{(row.event || '').replace(/_/g,' ')}</td>
                    <td className="px-4 py-3 font-mono text-xs text-gray-600 dark:text-gray-400">{row.recipient}</td>
                    <td className="px-4 py-3">{badge(row.status)}</td>
                    <td className="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400 max-w-[120px] truncate">{row.message_id || '—'}</td>
                    <td className="px-4 py-3 text-red-500 text-xs max-w-[150px] truncate" title={row.error}>{row.error || '—'}</td>
                    <td className="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap text-xs">{new Date(row.created_at).toLocaleString('en-IN')}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {logs.pages > 1 && (
          <div className="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <p className="text-sm text-gray-500">Page {page} of {logs.pages}</p>
            <div className="flex gap-2">
              <button onClick={() => setPage(p => Math.max(1, p - 1))} disabled={page === 1}
                className="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                Previous
              </button>
              <button onClick={() => setPage(p => Math.min(logs.pages, p + 1))} disabled={page === logs.pages}
                className="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                Next
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

// ─── App Shell ────────────────────────────────────────────────────────────────

function App() {
  const page = window.wcWan?.page || 'wc-wan';

  const renderPage = () => {
    if (page === 'wc-wan-settings')  return <SettingsPage />;
    if (page === 'wc-wan-templates') return <TemplatesPage />;
    if (page === 'wc-wan-logs')      return <LogsPage />;
    return <DashboardPage />;
  };

  return (
    <ToastProvider>
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white">
        <div className="max-w-6xl mx-auto px-4 py-8">
          {renderPage()}
        </div>
      </div>
    </ToastProvider>
  );
}

const root = ReactDOM.createRoot(document.getElementById('wc-wan-app'));
root.render(<App />);
