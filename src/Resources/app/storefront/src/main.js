const PluginManager = window.PluginManager;

PluginManager.register('FastOrder', () => import('./fast-order/fast-order.plugin'), '.fast-order-form');
