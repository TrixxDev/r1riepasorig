<style>
  .tippy-tooltip.light-theme {
    background-color: #000000;
    color: #fff;
    box-shadow: 0 0 20px 4px rgba(0, 0, 0, 0.15);
    border: 1px solid #000;
    border-radius: 4px;
  }
  
  .tippy-popper[x-placement^=top] .tippy-tooltip.light-theme .tippy-arrow {
    border-top-color: #000;
    border-width: 7px;
    bottom: -7px;
  }
  
  .tippy-popper[x-placement^=bottom] .tippy-tooltip.light-theme .tippy-arrow {
    border-bottom-color: #000;
    border-width: 7px;
    top: -7px;
  }
  
  .tippy-popper[x-placement^=left] .tippy-tooltip.light-theme .tippy-arrow {
    border-left-color: #000;
    border-width: 7px;
    right: -7px;
  }
  
  .tippy-popper[x-placement^=right] .tippy-tooltip.light-theme .tippy-arrow {
    border-right-color: #000;
    border-width: 7px;
    left: -7px;
  }
  
  .tippy-tooltip.light-theme .tippy-content {
    text-align: left;
    padding: 10px;
    line-height: 1.5;
    background-color: transparent;
    color: #fff!important;
  }

  .dot {
    height: 16px;
    width: 16px;
    border-radius: 50%;
    display: inline-block;
    margin-left: 7px;
    margin-bottom: -5px;
    margin-right: 6px;
    border: 1px solid #00000045;
  }

  .dot.red {
    background-color: lightgrey;
    color: lightgrey;
  }

  .dot.green {
    background-color: #4cbb6c;
    color: #4cbb6c;
  }

  .dot.yellow {
    background-color: #FFFF00;
    color: #FFFF00;
  }

  .dot.half-green {
    background-color: #4cbb6c;
    border-radius: 0 16px 16px 0;
    width: 10px;
    color: #4cbb6c;
    margin-left: 11px;
  }

  .dot.half-yellow {
    background-color: #FFFF00;
    border-radius: 0 16px 16px 0;
    width: 10px;
    margin-left: 11px;
    color: #FFFF00;
  }

  .dot-availability {
    width: 29px;
  }
</style>

