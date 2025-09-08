import { ApplicationConfig, importProvidersFrom, isDevMode, provideZoneChangeDetection } from '@angular/core';
import { HttpClient, provideHttpClient, withFetch, withInterceptorsFromDi, withNoXsrfProtection } from '@angular/common/http';

// 3rd-Party plugins variables
import { TranslateLoader, TranslateModule } from '@ngx-translate/core';


import { BrowserModule, provideClientHydration, withHttpTransferCacheOptions } from '@angular/platform-browser';
import { provideServiceWorker } from '@angular/service-worker';
import { AppRoutingModule } from './app.routes';

import { CoreModule, JsonLdModule, createTranslateLoader } from './core';

export const appConfig: ApplicationConfig = {
  providers: [
   importProvidersFrom(
    BrowserModule, 
    AppRoutingModule, 
    JsonLdModule,
    CoreModule,
    TranslateModule.forRoot({
      loader: {
        provide: TranslateLoader,
        useFactory: (createTranslateLoader),
        deps: [HttpClient]
      }
    })),
    provideClientHydration(withHttpTransferCacheOptions({
      includePostRequests: true
    })),
    provideServiceWorker('ngsw-worker.js', {
        enabled: !isDevMode(),
        registrationStrategy: 'registerWhenStable:30000'
    }),
    provideZoneChangeDetection({ eventCoalescing: true }),
    provideHttpClient(
      withFetch(),
      withNoXsrfProtection(),
      withInterceptorsFromDi()
    ),    
  ]
};
