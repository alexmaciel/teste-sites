import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

// 3rd-Party plugins variables

import { CompanyRoutingModule } from './company-routing.module';
import { CompanyComponent } from './company.component';

import { 
  NgxOptimizedImageModule,
  SharedModule 
} from '../../shared';

@NgModule({
  declarations: [
    CompanyComponent
  ],
  imports: [
    CommonModule,
    CompanyRoutingModule,
    NgxOptimizedImageModule,
    SharedModule
  ]
})
export class CompanyModule { }
