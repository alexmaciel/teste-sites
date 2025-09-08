import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

// 3rd-Party plugins variables

import { CompanyRoutingModule } from './company-routing.module';
import { CompanyComponent } from './company.component';

import { CoreModule } from '../../core';

@NgModule({
  declarations: [
    CompanyComponent
  ],
  imports: [
    CommonModule,
    CompanyRoutingModule,
    CoreModule
  ]
})
export class CompanyModule { }
