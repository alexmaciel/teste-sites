import { Component, Input } from '@angular/core';

import { BlogListComponent } from '../blog-list/blog-list.component';

@Component({
  selector: 'app-blog-sort',
  templateUrl: './blog-sort.component.html'
})
export class BlogSortComponent {
  @Input() posts!: BlogListComponent;

  // sorting
  sort(column: string) { 
    this.posts.sort(column);
  }    
}
