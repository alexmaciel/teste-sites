import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

// 3rd-Party plugins variables
import { InlineSVGModule } from 'ng-inline-svg-2';

import { ScriptsInitComponent } from './scripts-init/scripts-init.component';

// Components
import { ContentComponent } from './components/content/content.component';
import { HeaderComponent } from './components/header/header.component';
import { FooterComponent } from './components/footer/footer.component';
import { AsideComponent } from './components/aside/aside.component';
// Cookies
import { CookiesComponent } from "./components/cookies/cookies.component";
// Directives
import { ToggleMenuDirective } from './directives/toggle-menu.directive';

import { LayoutRoutingModule } from './layout-routing.module';
import { LayoutComponent } from './layout.component';

import { NgxSwiperModule, SharedModule } from "../shared";

@NgModule({
  declarations: [
    LayoutComponent,
    HeaderComponent,
    ContentComponent,
    FooterComponent,
    AsideComponent,
    // Directives
    ToggleMenuDirective,
    // Script
    ScriptsInitComponent
  ],
  imports: [
    CommonModule,
    LayoutRoutingModule,
    InlineSVGModule,
    NgxSwiperModule,
    SharedModule,
    // Cookies
    CookiesComponent,    
]
})
export class LayoutModule { }
