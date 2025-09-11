import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TranslateModule } from '@ngx-translate/core';

import { TouRoutingModule } from './tou-routing.module';
import { TouComponent } from './tou.component';
// Components
import { PrivacyComponent } from './privacy/privacy.component';
import { UseComponent } from './use/use.component';

import { CoreModule } from '../../core';

@NgModule({
  declarations: [
    TouComponent,
    PrivacyComponent,
    UseComponent
  ],
  imports: [
    CommonModule,
    TouRoutingModule,
    TranslateModule.forChild(),
    CoreModule
  ]
})
export class TouModule { }
