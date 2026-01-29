<?php
/**
 * Tryouts Module Routes
 */
return function($router, $moduleName) {
    // ========== ADMIN ROUTES - LOCATIONS ==========

    $router->get('/admin/tryout-locations', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'listLocations');
    $router->get('/admin/tryout-locations/view', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'viewLocation');
    $router->get('/admin/tryout-locations/create', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'createLocationForm');
    $router->post('/admin/tryout-locations/create', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'createLocation');
    $router->get('/admin/tryout-locations/edit', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'editLocationForm');
    $router->post('/admin/tryout-locations/update', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'updateLocation');
    $router->post('/admin/tryout-locations/delete', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'deleteLocation');
    $router->post('/admin/tryout-locations/toggle-active', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'toggleLocationActive');

    // ========== ADMIN ROUTES - TRYOUTS ==========

    $router->get('/admin/tryouts', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'listTryouts');
    $router->get('/admin/tryouts/view', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'viewTryout');
    $router->get('/admin/tryouts/create', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'createTryoutForm');
    $router->post('/admin/tryouts/create', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'createTryout');
    $router->get('/admin/tryouts/edit', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'editTryoutForm');
    $router->post('/admin/tryouts/update', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'updateTryout');
    $router->post('/admin/tryouts/delete', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'deleteTryout');
    $router->post('/admin/tryouts/update-status', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'updateTryoutStatus');

    // ========== ADMIN ROUTES - CSV IMPORT ==========

    $router->get('/admin/tryouts/import', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'importForm');
    $router->post('/admin/tryouts/import', 'Modules\\tryouts\\Controllers\\TryoutAdminController', 'processImport');

    // ========== ADMIN ROUTES - REGISTRATIONS ==========

    $router->get('/admin/tryout-registrations', 'Modules\\tryouts\\Controllers\\TryoutRegistrationController', 'listRegistrations');
    $router->get('/admin/tryout-registrations/view', 'Modules\\tryouts\\Controllers\\TryoutRegistrationController', 'viewRegistration');
    $router->post('/admin/tryout-registrations/update-payment', 'Modules\\tryouts\\Controllers\\TryoutRegistrationController', 'updatePaymentStatus');
    $router->post('/admin/tryout-registrations/update-attendance', 'Modules\\tryouts\\Controllers\\TryoutRegistrationController', 'updateAttendanceStatus');
    $router->post('/admin/tryout-registrations/add-note', 'Modules\\tryouts\\Controllers\\TryoutRegistrationController', 'addNote');
    $router->post('/admin/tryout-registrations/cancel', 'Modules\\tryouts\\Controllers\\TryoutRegistrationController', 'cancelRegistration');
    $router->post('/admin/tryout-registrations/promote-waitlist', 'Modules\\tryouts\\Controllers\\TryoutRegistrationController', 'promoteFromWaitlist');

    // ========== PLAYER ROUTES ==========

    $router->get('/tryouts', 'Modules\\tryouts\\Controllers\\TryoutPlayerController', 'browse');
    $router->get('/tryouts/view', 'Modules\\tryouts\\Controllers\\TryoutPlayerController', 'viewTryout');
    $router->get('/tryouts/register', 'Modules\\tryouts\\Controllers\\TryoutPlayerController', 'registerForm');
    $router->post('/tryouts/register', 'Modules\\tryouts\\Controllers\\TryoutPlayerController', 'processRegistration');
    $router->get('/tryouts/confirmation', 'Modules\\tryouts\\Controllers\\TryoutPlayerController', 'confirmation');
    $router->get('/tryouts/my-registrations', 'Modules\\tryouts\\Controllers\\TryoutPlayerController', 'myRegistrations');
    $router->post('/tryouts/cancel-registration', 'Modules\\tryouts\\Controllers\\TryoutPlayerController', 'cancelMyRegistration');
};
