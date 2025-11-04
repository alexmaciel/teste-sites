import { APP_BASE_HREF, Location } from '@angular/common';
import { mergeApplicationConfig, ApplicationConfig } from '@angular/core';
import { provideServerRendering } from '@angular/platform-server';
import { appConfig } from './app.config';

import { HttpClient } from '@angular/common/http';
import { TranslateLoader, TranslateService } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';
import { LocalizeRouterHttpLoader } from '@gilsdav/ngx-translate-router-http-loader';
import { LocalizeRouterSettings } from '@gilsdav/ngx-translate-router';

import { environment } from '../environments/environment';

// Loader para SSR
export function HttpLoaderFactory(http: HttpClient) {
  return new TranslateHttpLoader(http, './assets/i18n/', '.json');
}


export function LocalizeLoaderFactory(translate: TranslateService, location: Location, settings: LocalizeRouterSettings, http: HttpClient) {
  return new LocalizeRouterHttpLoader(translate, location, settings, http, `${environment.appUrl}assets/locales.json`);
}

const serverConfig: ApplicationConfig = {
  providers: [
    provideServerRendering(),
    { provide: APP_BASE_HREF, useValue: '/' },
    {
      provide: TranslateLoader,
      useFactory: HttpLoaderFactory,
      deps: [HttpClient]
    },
    {
      provide: LocalizeRouterHttpLoader,
      useFactory: LocalizeLoaderFactory,
      deps: [TranslateService, Location, LocalizeRouterSettings, HttpClient]
    }    
  ]
};

export const config = mergeApplicationConfig(appConfig, serverConfig);
