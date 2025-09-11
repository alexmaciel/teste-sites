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
// Directives
import { ToggleMenuDirective } from './directives/toggle-menu.directive';

import { LayoutRoutingModule } from './layout-routing.module';
import { LayoutComponent } from './layout.component';

import { CoreModule } from '../core';
import { NgxSwiperModule } from "../shared";

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
    CoreModule,
    NgxSwiperModule
]
})
export class LayoutModule { }
