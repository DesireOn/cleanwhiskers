import { startStimulusApp } from '@symfony/stimulus-bundle';

export const app = startStimulusApp(
    import.meta.glob('./controllers/**/*_controller.js', { eager: true })
);
// Register any custom, 3rd party controllers here
// app.register('some-controller-name', SomeImportedController);
