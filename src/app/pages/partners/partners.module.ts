import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { InlineSVGModule } from 'ng-inline-svg-2';

import { PartnersRoutingModule } from './partners-routing.module';
import { PartnersComponent } from './partners.component';

import { 
  NgxOptimizedImageModule,
  SharedModule 
} from '../../shared';

@NgModule({
  declarations: [
    PartnersComponent
  ],
  imports: [
    CommonModule,
    PartnersRoutingModule,
    InlineSVGModule,
    NgxOptimizedImageModule,
    SharedModule
  ]
})
export class PartnersModule { }
