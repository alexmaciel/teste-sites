import { NgModule } from '@angular/core';
import { provideRouter, RouteReuseStrategy, RouterModule, Routes, TitleStrategy, withDisabledInitialNavigation } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { Location } from '@angular/common';

import { CacheMechanism, LocalizeParser, LocalizeRouterModule, LocalizeRouterSettings, ManualParserLoader, withLocalizeRouter } from '@gilsdav/ngx-translate-router';
import { TranslateService } from '@ngx-translate/core';
import { LocalizeRouterHttpLoader } from '@gilsdav/ngx-translate-router-http-loader';
import { 
    CustomReuseStrategy,
    TranslateTitleStrategy,
    TranslationService,
    SeoService, 
} from './core';


//@ts-ignore - We ignore this because there is no initialize method on the HTMLElement
export function ManualLoaderFactory(translate: TranslateService, location: Location, settings: LocalizeRouterSettings) {
    return new ManualParserLoader(translate, location, settings, ['pt', 'en', 'es'], 'ROUTES.', '!');
}

export function HttpLoaderFactory(translate: TranslateService, location: Location, settings: LocalizeRouterSettings, http: HttpClient) {
  return new LocalizeRouterHttpLoader(translate, location, { ...settings, alwaysSetPrefix: true }, http, `./assets/locales.json`);
}

export const routes: Routes = [
    {
        path: '',
        loadChildren: () =>
          import('./layout/layout.module').then((m) => m.LayoutModule),
    },   
    {
        path: 'error',
        loadChildren: () =>
          import('./errors/errors.module').then((m) => m.ErrorsModule),
    },      
    { path: '**', redirectTo: 'error' },     
];

@NgModule({
    imports: [
        RouterModule.forRoot(routes, { initialNavigation: 'disabled' })
    ],
    providers: [
        provideRouter(routes, 
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
        })),
        { 
            provide: TitleStrategy, useClass: TranslateTitleStrategy,
        },     
        {
            provide: RouteReuseStrategy, useClass: CustomReuseStrategy
        },
        TranslationService,
        SeoService
    ],
    exports: [RouterModule, LocalizeRouterModule]
})

export class AppRoutingModule { }