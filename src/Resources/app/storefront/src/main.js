const PluginManager = window.PluginManager;

PluginManager.register('FastOrder', () => import('./fast-order/fast-order.plugin'), '.fast-order-form');

PluginManager.register('SearchPlugin', () => import('./search/search.plugin'), '.search-field');
