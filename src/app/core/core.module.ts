import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

// 3rd-Party plugins variables
import { InlineSVGModule } from 'ng-inline-svg-2';
import { NgSelectModule } from '@ng-select/ng-select';
import { NgbModalModule } from '@ng-bootstrap/ng-bootstrap';
import { LocalizeRouterModule } from '@gilsdav/ngx-translate-router';
import { TranslateModule } from '@ngx-translate/core';
import { QuillModule } from 'ngx-quill';

// Pagination
import { NgPagination } from './helpers/paginator/ng-pagination/ng-pagination.component';
import { PaginatorComponent } from './helpers/paginator/paginator.component';

// Pipes
import { 
  TruncatetextPipe,
  DateLocalePipe,
  SafePipe
} from './pipes';

// Components
import { 
  CasesComponent,
  BlogComponent,
  VideoComponent,
  // Map
  LocationComponent,
  // Request
  RequestComponent,
  FormComponent,
} from './components';

// Plugins
import { 
  NgxSwiperModule,
  NgxYouTubePlayerModule,
  NgxOptimizedImageModule
 } from '../shared';

@NgModule({
  declarations: [
    // Pipes
    TruncatetextPipe,
    DateLocalePipe,
    SafePipe,
    // Components
    CasesComponent,
    VideoComponent, 
    BlogComponent,
    // Map
    LocationComponent,
    // Pagination
    NgPagination,
    PaginatorComponent,
    // Request
    RequestComponent,    
    FormComponent
  ],
  imports: [
    CommonModule,
    FormsModule, 
    ReactiveFormsModule,
    // Translate
    TranslateModule,
    LocalizeRouterModule,    
    // Plugins
    NgxSwiperModule,
    NgxYouTubePlayerModule,
    NgxOptimizedImageModule,
    NgSelectModule,
    QuillModule,
    InlineSVGModule,  
    NgbModalModule,
  ],
  exports: [
    // Translate
    TranslateModule,
    LocalizeRouterModule,  
    // Pipes
    TruncatetextPipe,
    DateLocalePipe,
    SafePipe,
    // Components
    CasesComponent,
    VideoComponent, 
    BlogComponent,
    // Map
    LocationComponent,
    // Request
    RequestComponent,
    // Pagination
    NgPagination,
    PaginatorComponent,          
  ],
  schemas: [CUSTOM_ELEMENTS_SCHEMA]
})
export class CoreModule { }
