/**
 * Renders into a toolbar button.
 * 
 * @author Lucian CIOROGA
 */
Button = new Class({
    /**
     * @param {String} id       The id of the button (should be unique).
     * @param {Object} options  An option map for this button.
     * 
     * Options are:
     *      command     The command that will be executed (using execCommand) when the button is pressed.
     *      con         A path to the file containing the icon of the button.
     *      hover       A path to the file containing the icon that will be displayed when hovering over a button
     */
    initialize: function(id, options) {
        this.id = id;
        this.options = options;
    },
    
    /**
     * Renders this button for a given editor.
     * @param {Editor} editor   The editor in which this button exists.
     */
    render: function(editor) {
        return this.build(this, editor);
    },
    
    /**
     * Builds this button for a given editor.
     * @param {Button} self     Reference to itself.
     * @param {Editor} editor   The editor in which this button exists.
     */
    build: function(self, editor) {
        return component = new Element('img', {
            'src': self.options.icon,
            'styles': {
                'cursor': 'pointer'
            },
            'events': {
                'click': function() {
                    self.execute(editor, null);
                },
                'mouseenter': function() {
                    this.src = self.options.hover;
                },
                'mouseleave': function() {
                    this.src = self.options.icon;
                }
            }
        });
    },
    
    /**
     * @param {Editor} editor   The editor for which to execute the button's action.
     * @param {Object} value    The value for the <code>execCommand</code> function.
     */
    execute: function(editor, value) {
        editor.restore();
        
        try {
            editor.content.execCommand(this.options.command, false, value);
        } catch (e) {
            alert("failed in executing " + this.options.command + ". Cause: " + e.message);
        }
    
        editor.focus();
    }
});

SingleValueButton = new Class({
    Extends: Button,
    initialize: function(id, options) {
        this.parent(id, options);
    },
    
    execute: function(editor, value) {
        var url = '';
        if (this.id == 'IMG') {
            // TODO: popup image uploader
        } else {
            url = window.prompt('URL:')
        }
        
        if (!isEmpty(url)) {
            this.parent(editor, url);
        } else {
            alert('You need to provide a web address for the link to work.');
        }
    }
});

/**
 * Renders into a button that allows choosing a color from a popup color table.
 * 
 * @author Lucian CIOROGA
 */
ColorChooserButton = new Class({
    Extends: Button,
    /**
     * Constructor.
     * @param {String} id       The id of the button (should be unique).
     * @param {String} color    The initial color (CSS).
     * @param {Object} options  An option map for this button.
     * 
     * Options are:
     *      *           Inherited from Button.
     *      colors      Function that returns a bidimensional array of colors. This allows the user to override the default color table that will be shown onmouseover.
     */
    initialize: function(id, color, options) {
        this.parent(id, options);
        this.color = color;
        this.disposeChooser = false;
    },
    
    /**
     * @return The table of colors.
     */
    getColors: function() {
        if (this.options.colors != null) {
            return this.options.colors();
        } else {
            return [    
                ['#000', '#930', '#330', '#030', '#036', '#007', '#339', '#333'],
                ['#700', '#f60', '#770', '#070', '#077', '#00f', '#669', '#777'],
                ['#f00', '#f90', '#9c0', '#396', '#3cc', '#36f', '#707', '#999'],
                ['#f0f', '#fc0', '#ff0', '#0f0', '#0ff', '#0cf', '#936', '#ccc'],
                ['#f9c', '#fc9', '#ff9', '#cfc', '#cff', '#9cf', '#c97', '#fff']
            ];
        }
    },
    
    /**
     * @param {ColorChooserButton} self
     * @param {Editor} editor
     */
    /*@Override*/ build: function(self, editor) {
        // the color chooser <div> - the popup that will apear onmouseover
        this.colorChooser = new Element('div', {
            'styles': {
                'background-color': '#eee',
                'position': 'absolute'
            }
        });
        
        var i = 0;
        var j = 0;
        var x = 0;
        var y = 0;
        $each(this.getColors(), function(row) {
            $each(row, function(c) {
                var colorDiv = new Element('div', {
                    'styles': {
                        'background-color': c,
                        'height': '14px',
                        'width': '14px',
                        'float': 'left',
                        'position': 'absolute'
                    },
                    'events': {
                        'click': function() {
                            self.color = c;
                            self.execute(editor, c);
                        },
                        'mouseenter': function(){
                            this.setStyle('width', 12);
                            this.setStyle('height', 12);
                            this.setStyle('border', '1px solid #fff');
                        },
                        'mouseleave': function(){
                            this.setStyle('width', 14);
                            this.setStyle('height', 14);
                            this.setStyle('border', 'none');
                        }
                    }
                });
                
                colorDiv.set('html', "&nbsp;");
                colorDiv.setStyle('font-size', '1px');
                
                colorDiv.setStyle('top', i * 18 + 4);
                colorDiv.setStyle('left', j * 18 + 4);
                
                j++;
                
                self.colorChooser.adopt(colorDiv);
            });
            
            i++;
            
            if (y < j) {
                y = j;
            }
            
            j = 0;
        }); 
        
        x = i;
        
        var height = x * 18 + 4;
        var width = y * 18 + 4;
        
        this.colorChooser.setStyle('width', width);
        this.colorChooser.setStyle('height', height);
    
        // a new type of button
        var component = new Element('div', {
            'styles': {
                'height': '100%',
                'width': '18px',
                'text-align': 'center',
                'cursor': 'pointer'
            },
            'events': {
                'click': function() {
                    if (this.disposeChooser) {
                        this.adopt(self.colorChooser);

                        if (Browser.Engine.trident) {
                            self.colorChooser.setStyle('left', this.getCoordinates().left + 2);
                        }
                    } else {
                        self.colorChooser.dispose();
                    }
                    
                    this.disposeChooser = !this.disposeChooser;
                }
            }
        });
    
        // loads the default button
        var button = this.parent(self, editor);
        
        // builds the color preview
        this.colorPreview = new Element('div', {
            'styles': {
                'margin': '1px 1px 1px 1px',
                'background-color': self.color,
                'border': '1px solid #ccc',
                'font-size': '1px'
            }
        });
        
        this.colorPreview.setStyle('height', '4px');
        this.colorPreview.setStyle('width', '15px');
        
        this.colorPreview.set('html', "&nbsp;");
        
        // populates the color component (the actual color chooser buttton)
        component.adopt(button);
        component.adopt(this.colorPreview);
        
        return component;
    },
    
    /*@Override*/ execute: function(editor, value) {
        if (value != null) {
            value =     value.charAt(0) + 
                        value.charAt(1) + value.charAt(1) +
                        value.charAt(2) + value.charAt(2) +
                        value.charAt(3) + value.charAt(3);

            this.colorPreview.setStyle('background-color', value);
            this.colorChooser.dispose();

            this.parent(editor, value);
        }
    }
});
/**
 * Renders into a Combo that allows choosing a style from a drop down list.
 * The drop down list's items have the style already applied.
 * 
 * @author Lucian CIOROGA
 */
StyleChooserButton = new Class({
    Extends: Button,
    /**
     * Constructor.
     * @param {String} id       The id of the button (should be unique).
     * @param {String} style    The initial value for the style property.
     * @param {Object} options  An option map for this button.
     * 
     * Options are:
     *      *           Inherited from Button.
     *      values      Map of possible values; the <key> is the label that will be displayed, 
     *                  while the <value> is the value that will be set.
     *      stylize     Function that applies a style to an element displayed as an option in the select.
     *                  It gets 2 parameters: <element> and <value>. Basically, it allows the user to apply 
     *                  format an option according to a style value associated with that option.
     */
    initialize: function(id, style, options) {
        options.icon = '../common/images/ggEdit_images/arrow.gif';
        options.hover = '../common/images/ggEdit_images/arrow-hover.gif';
        
        this.style = style;
    
        this.parent(id, options);
    },

    /**
     * @param {StyleChooserButton} self
     * @param {Editor} editor
     */
    /*@Override*/ build: function(self, editor) {
        // the color chooser <div> - the popup that will apear onmouseover
        this.styleChooser = new Element('div', {
            'styles': {
                'background-color': '#f5f5f5',
                'border': '2px solid #ccc',
                'position': 'absolute',
                'padding': '5px'
            }
        });
    
        this.stylePreview = new Element('div', {
            'styles': {
                'float': 'left',
                'font-family': 'Trebuchet MS',
                'font-size': '12px',
                'padding-left': '5px'
            }
        });
        this.stylePreview.set('text', this.style);
        
        if (Browser.Engine.webkit) {
            // Safari text positioning fix
            this.stylePreview.setStyle('padding-top', '2px');
        }
        
        $each(this.options.values, function(data, key) {
            var styleLine = new Element('div', {
                'styles': {
                    'text-align': 'left',
                    'color': '#333',
                    'font-family': 'Trebuchet MS',
                    'font-size': '12px'
                },
                'events': {
                    'click': function() {
                        this.setStyle('text-decoration', 'none');
                        self.styleChooser.dispose();
                        self.stylePreview.set('text', key);
                        self.execute(editor, data);
                    },
                    'mouseenter': function() {
                        this.setStyle('text-decoration', 'underline');
                    },
                    'mouseleave': function() {
                        this.setStyle('text-decoration', 'none');
                    }
                }
            });
            
            styleLine.set('text', key);
            self.options.stylize(styleLine, data);
            
            self.styleChooser.adopt(styleLine);
        }); 
    
        // a new type of button
        var component = new Element('div', {
            'styles': {
                'height': '100%',
                'text-align': 'center',
                'cursor': 'pointer',
                'width': '100px'
            },
            'events': {
                'mouseenter': function() {
                    self.styleChooser.setOpacity(0);
                    this.adopt(self.styleChooser);
                    
                    var thisCoordinates = this.getCoordinates();
                    var chooserCoordinates = self.styleChooser.getCoordinates();

                    if (chooserCoordinates.width < thisCoordinates.width) {
                        if (Browser.Engine.trident) {
                            self.styleChooser.setStyle('width', thisCoordinates.width + 4);
                        } else {
                            self.styleChooser.setStyle('width', thisCoordinates.width - 12);                            
                        }
                    }
                    
                    self.styleChooser.setStyle('left', thisCoordinates.left + 1);
                    self.styleChooser.setStyle('top', thisCoordinates.top + 19);
                    
                    self.styleChooser.setOpacity(1);
                },
                'mouseleave': function() {  
                    self.styleChooser.dispose();
                }
            }
        });
    
        // loads the default button
        var button = this.parent(self, editor);

        button.setStyle('float', 'right');
        component.adopt(button);        
        component.adopt(this.stylePreview);
        
        return component;
    }
});
/**
 * @author Lucian CIOROGA
 */
Separator = new Class({
    Extends: Button,
    /**
     * @param {Separator} self
     * @param {Editor} editor
     */
    /*@Override*/ build: function(self, editor) {
        return new Element('img', {
            'src': self.options.icon
        });
    }
});
// DEFAULT BUTTONS

var S = new Separator('S', {icon: "../common/images/ggEdit_images/separator.gif"});

var IMG     = new SingleValueButton('IMG', {command: "insertimage", icon: "../common/images/ggEdit_images/insertimage.gif", hover: "../common/images/ggEdit_images/insertimage-hover.gif"});
var HL  = new SingleValueButton('HL', {command: "createlink", icon: "../common/images/ggEdit_images/createlink.gif", hover: "../common/images/ggEdit_images/createlink-hover.gif"});

var B   = new Button('B', {command: "Bold", icon: "../common/images/ggEdit_images/bold.gif", hover: "../common/images/ggEdit_images/bold-hover.gif"});
var I   = new Button('I', {command: "Italic", icon: "../common/images/ggEdit_images/italic.gif", hover: "../common/images/ggEdit_images/italic-hover.gif"});
var U   = new Button('U', {command: "Underline", icon: "../common/images/ggEdit_images/underline.gif", hover: "../common/images/ggEdit_images/underline-hover.gif"});
var OL  = new Button('OL', {command: "InsertOrderedList", icon: "../common/images/ggEdit_images/orderedlist.gif", hover: "../common/images/ggEdit_images/orderedlist-hover.gif"});
var UL  = new Button('UL', {command: "InsertUnOrderedList", icon: "../common/images/ggEdit_images/unorderedlist.gif", hover: "../common/images/ggEdit_images/unorderedlist-hover.gif"});
var JL  = new Button('JL', {command: "JustifyLeft", icon: "../common/images/ggEdit_images/justifyleft.gif", hover: "../common/images/ggEdit_images/justifyleft-hover.gif"});
var JC  = new Button('JC', {command: "JustifyCenter", icon: "../common/images/ggEdit_images/justifycenter.gif", hover: "../common/images/ggEdit_images/justifycenter-hover.gif"});
var JR  = new Button('JR', {command: "JustifyRight", icon: "../common/images/ggEdit_images/justifyright.gif", hover: "../common/images/ggEdit_images/justifyright-hover.gif"});

var FG  = new ColorChooserButton('FG', '#000', {command: "ForeColor", icon: "../common/images/ggEdit_images/forecolor.gif", hover: "../common/images/ggEdit_images/forecolor-hover.gif"});
var BG;

if (Browser.Engine.gecko || Browser.Engine.presto) {
    BG = new ColorChooserButton('BG', '#000', {
        command: "HiliteColor",
        icon: "../common/images/ggEdit_images/backcolor.gif",
        hover: "../common/images/ggEdit_images/backcolor-hover.gif"
    });
} else {
    BG = new ColorChooserButton('BG', '#000', {
        command: "BackColor",
        icon: "../common/images/ggEdit_images/backcolor.gif",
        hover: "../common/images/ggEdit_images/backcolor-hover.gif"
    });
}

var FS  = new StyleChooserButton('FS', 'normal', {
    command: 'FontSize',
    values: {'x-small': '1', 'small': '2', 'normal': '3', 'big': '5', 'x-big': '6'},
    stylize: function(element, value) {
        switch (value) {
            case '1': element.style.fontSize = '8px'; break;
            case '2': element.style.fontSize = '10px'; break;
            case '3': element.style.fontSize = '14px'; break;
            case '5': element.style.fontSize = '20px'; break;
            case '6': element.style.fontSize = '24px'; break;
        }
    }
});

var FF  = new StyleChooserButton('FF', 'Trebuchet', {
    command: 'FontName',
    stylize: function(element, value) {
        element.style.fontFamily = value;
    },
    value: 'Trebuchet MS',
    values: {
        'Trebuchet': 'Trebuchet MS', 
        'Arial': 'Arial', 
        'Tahoma': 'Tahoma', 
        'Verdana': 'Verdana', 
        'Times': 'Times New Roman',
        'Courier': 'Courier New',
        'Garamond': 'Garamomd'
    }
});
/**
 * Creates the editor with editing area and toolbar.
 * 
 * @author Lucian CIOROGA
 */
Editor = new Class({
    /**
     * Constructor.
     * @param {Object} options
     * 
     * Options are:
     *      buttons     The list of buttons that will be displayed. If this option is not set, the Editor will use a default list.
     */
    initialize: function(options) {
        this.options = options;
        
        this.textarea = new Element('iframe', {
            'styles': {
                'border': '1px solid #ccc',
                'background-color': '#fff',
                'width': '100%',
                'height': '100%',
                'margin': '1px 0px 0px 0px'
            }
        });
    },
    
    /**
     * @return  The list of buttons (in order) that will be displayed in the toolbar.
     */
    getButtons: function() {
        if (this.options && this.options.buttons) {
            return this.options.buttons();
        } else {
            return [B, I, U, JL, JC, JR, S, OL, UL, S, FS, FF, S, FG, BG, S, HL];
        }
    },

    /**
     * @param {Element} container   The DOM element given where the editor should be added.
     *                              Make sure that the <code>container</code> is loaded when the editor is rendered.
     */
    render: function(container) {
        this.container = container;
        
        this.container.setStyle('background-color', '#f5f5f5');
        this.container.setStyle('border', '1px solid #ccc');
        this.container.setStyle('padding', '1px 1px 1px 1px');
        
        var editor = this;
        var buttons = this.getButtons();
        
        // building toolbar table
        this.toolbar = new Element('div', {
            'styles': {
                'height': '20px'
            }
        });
        
        // creating toolbar
        $each(buttons, function(button) {
            var borderStyle;
            if (button instanceof Separator) {
                borderStyle = 'none';
            } else {
                borderStyle = '1px solid #ccc';
            }
        
            var buttonContainer = new Element('div', {
                'styles': {
                    'float': 'left', 
                    'border': borderStyle, 
                    'height': '19px',
                    'margin': '0px 1px 0px 0px'
                }
            });
            
            var component = button.render(editor);
            buttonContainer.adopt(component);
            editor.toolbar.adopt(buttonContainer);
        });
        
        this.container.adopt(this.toolbar);
        this.container.adopt(this.textarea);
        
        if (!Browser.Engine.trident) {
            // fix dimension problems
            var containerCoordinates = this.container.getCoordinates();
            var frameCoordinates = this.textarea.getCoordinates();
            
            var verticalDiff = frameCoordinates.bottom - containerCoordinates.bottom;
            var horizontalDiff = frameCoordinates.right - containerCoordinates.right;
            
            this.textarea.setStyle('width', frameCoordinates.width - horizontalDiff - 4);
            this.textarea.setStyle('height', frameCoordinates.height - verticalDiff - 4);
        }
        
        // setting designMode = ON
        this.textarea.addEvent('load', function() {
            if (Browser.Engine.presto) {
                editor.content = this.contentWindow.document;
            } else if (Browser.Engine.trident) {
                editor.content = this.contentWindow.document;
            } else if (Browser.Engine.gecko) {
                editor.content = this.contentDocument;
            } else if (Browser.Engine.webkit) {
                editor.content = this.contentDocument;
            }
        
            if (editor.content != null) {
                if (Browser.Engine.trident) {
                    $(editor.content.body).setStyle('border', 'none');
                }
                
                editor.content.designMode = "on";
    
                // storing selection
                if (Browser.Engine.trident) {
                    // IE hack
                    $(editor.content.body).addEvent('click', function(){
                        editor.lastSelection = editor.content.selection.createRange();
                    });
                    $(editor.content.body).addEvent('dblclick', function(){
                        editor.lastSelection = editor.content.selection.createRange();
                    });
                }
                
                // Checks if the user tried to set a value while the editor was not yet loaded.
                // If there is value stored, initializes the editor with the stored value.
                if (editor.value != null) {
                    editor.setValue(editor.value);
                }
            } else {
                editor.container.set('html', "Unknown browser. Could not load editor.");
            }
        });
        
        if (Browser.Engine.webkit || Browser.Engine.presto) {
            // Safari and Opera need a little back-push :P.
            this.textarea.fireEvent('load');
        }
    },
    
    /**
     * @return The HTML contained in the editor.
     */
    getValue: function() {
        return this.content.body.innerHTML;
    },
    
    /**
     * Sets a value to the editor.
     * @param {String} html     HTML string.
     */
    setValue: function(html) {
        this.value = html;
        
        // If the editor is fully loaded, update the contents of it.
        if (this.content != null) {
            this.content.body.innerHTML = this.value;
        }
    },
    
    /**
     * Focuses the editor area (iframe with designMode set to "on").
     * By default, it is called after clicking on a button and after the action associated with the button executes.
     */
    focus: function() {
        this.textarea.contentWindow.focus();
    },
    
    /**
     * Function used to prepare the editor for the execCommand function.
     */
    restore: function() {
        if (Browser.Engine.trident) {
            if (this.lastSelection) {
                this.lastSelection.select();
            }
        }
    }
});
