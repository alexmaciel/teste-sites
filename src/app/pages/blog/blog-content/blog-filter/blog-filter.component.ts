import { Component, Input, OnDestroy, OnInit } from '@angular/core';
import { Subscription } from 'rxjs';


import { BlogListComponent } from '../blog-list/blog-list.component';

@Component({
  selector: 'app-blog-filter',
  templateUrl: './blog-filter.component.html'
})
export class BlogFilterComponent {
  @Input() posts!: BlogListComponent;

}
