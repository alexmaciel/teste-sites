import { ApplicationConfig, importProvidersFrom, isDevMode, provideZoneChangeDetection } from '@angular/core';
import { HttpClient, HttpClientModule, provideHttpClient, withFetch, withInterceptorsFromDi, withNoXsrfProtection } from '@angular/common/http';
import { provideRouter, RouteReuseStrategy, withDisabledInitialNavigation } from '@angular/router';
import { Location } from '@angular/common';

// 3rd-Party plugins variables
import { CacheMechanism, LocalizeParser, LocalizeRouterSettings, ManualParserLoader, withLocalizeRouter } from '@gilsdav/ngx-translate-router';
import { LocalizeRouterHttpLoader } from '@gilsdav/ngx-translate-router-http-loader';
import { TranslateLoader, TranslateModule, TranslateService } from '@ngx-translate/core';

//@ts-ignore - We ignore this because there is no initialize method on the HTMLElement
export function ManualLoaderFactory(translate: TranslateService, location: Location, settings: LocalizeRouterSettings) {
    return new ManualParserLoader(translate, location, settings, ['pt', 'en', 'es'], 'ROUTES.', '!');
}

export function HttpLoaderFactory(translate: TranslateService, location: Location, settings: LocalizeRouterSettings, http: HttpClient) {
  return new LocalizeRouterHttpLoader(translate, location, { ...settings, alwaysSetPrefix: true }, http, `./assets/locales.json`);
}

import { BrowserModule, provideClientHydration, withHttpTransferCacheOptions } from '@angular/platform-browser';
import { provideServiceWorker } from '@angular/service-worker';
import { routes } from './app.routes';

import { CoreModule, CustomReuseStrategy, JsonLdModule, createTranslateLoader } from './core';
import { provideAnimations } from '@angular/platform-browser/animations';

export const appConfig: ApplicationConfig = {
  providers: [
    provideAnimations(),
    importProvidersFrom(
      BrowserModule, 
      HttpClientModule,
      JsonLdModule,
      CoreModule,
      TranslateModule.forRoot({
        loader: {
          provide: TranslateLoader,
          useFactory: createTranslateLoader,
          deps: [HttpClient]
        }
      })
    ),
    provideRouter(
      routes,
      withDisabledInitialNavigation(),
      withLocalizeRouter(routes, {
        parser: {
          provide: LocalizeParser,
          useFactory: HttpLoaderFactory,
          deps: [TranslateService, Location, LocalizeRouterSettings, HttpClient]
        },
        initialNavigation: true,
        cacheMechanism: CacheMechanism.Cookie,
        cookieFormat: '{{value}};{{expires:20}};path=/',
      })
    ),   
    provideClientHydration(withHttpTransferCacheOptions({
      includePostRequests: true
    })),
    provideServiceWorker('ngsw-worker.js', {
      enabled: !isDevMode(),
      registrationStrategy: 'registerWhenStable:30000'
    }),
    provideZoneChangeDetection({ eventCoalescing: true }),
    { provide: RouteReuseStrategy, useClass: CustomReuseStrategy },    
    provideHttpClient(
      withFetch(),
      withNoXsrfProtection(),
      withInterceptorsFromDi()
    )    
  ]
};
