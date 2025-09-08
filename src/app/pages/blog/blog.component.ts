import { Component } from '@angular/core';

@Component({
  selector: 'app-blog',
  templateUrl: './blog.component.html'
})
export class BlogComponent {

  constructor() {
    if (typeof document !== 'undefined') {
      document.body.setAttribute('data-mv-app-header-color', 'color');
    }     
  }
}
